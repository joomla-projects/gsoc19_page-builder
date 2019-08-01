<template>
	<grid-layout
		:layout="columns"
		:col-num="size"
		:is-draggable="true"
		:is-resizable="true"
		@layout-updated="reorder"
	>

		<!-- Columns -->
		<grid-item v-for="column in columns" :key="column.i"
			:class="['col-wrapper', column.element.type, column.element.options.offsetClass, column.element.options.class]"
			:i="column.i"
			:w="column.w"
			:h="1"
			:x="column.x"
			:y="column.y"
			@resize="resize"
			@resized="changeSize">
			<item :item="column.element" @delete="deleteElement({element: column.element, parent: grid})" @edit="editElement({element: column.element, parent: grid})"></item>
		</grid-item>
	</grid-layout>
</template>

<script>
  import {mapMutations} from 'vuex';

  export default {
    name: 'grid',
    props: {
      grid: {
        required: true,
        type: Object,
      },
    },
    computed: {
      nextIndex() {
        let index = 0;
        if (this.columns.length) {
          this.columns.forEach(col => {
            index = Math.max(index, col.i);
          });
        }
        return index + 1;
      },
      nextSpace() {
        let y = this.maxRow;
        let x = 0;

        this.columns.forEach(col => {
          if (col.y === y && col.x >= x) {
            x = col.x + col.w;

            if (x === this.size) {
              x = 0;
              y += 1;
            }
          }
        });

        return {x: x, y: y, w: this.size - x};
      },
      maxRow() {
        let maxY = 0;
        this.columns.forEach(col => maxY = Math.max(col.y, maxY));
        return maxY;
      },
    },
    data() {
      return {
        columns: [],
        size: 12,
      };
    },
    watch: {
      grid: {
        deep: true,
        handler() {
          this.mapElementChanges();
        }
      },
    },
    created() {
      this.mapGrid();
    },
    methods: {
      ...mapMutations([
        'deleteElement',
        'editElement',
        'setParent',
        'addElement'
      ]),
      mapGrid() {
        let x = 0;
        let y = 0;

        this.grid.children.forEach((child) => {
          const col = {
            i: this.nextIndex,
            w: child.type !== 'column' ? this.size : child.options.size || 1, // Takes care of elements other than 'column'
            h: 1,
            x: x,
            y: y,
            element: child,
          };

          x += col.w;
          if (x === this.size) {
            x = 0;
            y += 1;
          }
          this.columns.push(col);
        });
      },
      mapElementChanges() {
        // Search for removed children
        this.columns.forEach(col => {
          const found = this.grid.children.indexOf(col.element);
          if (found === -1) {
            // Remove column from grid
            this.columns.splice(this.columns.indexOf(col), 1);
          }
        });
        // Search for new children
        this.grid.children.forEach(child => {
          const found = this.columns.find(col => col.element === child);
          if (!found) {
            const newPosition = this.nextSpace;
            this.columns.push({
              i: this.nextIndex,
              w: newPosition.w,
              h: 1,
              x: newPosition.x,
              y: newPosition.y,
              element: child,
            });
          }
        });
      },
      getColumnByIndex(i) {
        return this.columns.find(col => col.i === i);
      },
      resize(i, newH, newW) {
        const resizedCol = this.getColumnByIndex(i);

        // Col gets more widely: Move following columns away
        if (resizedCol.w < newW) {
          this.moveToRight(resizedCol.x + resizedCol.w, resizedCol.y);
        }

        // Col gets narrower: Fill gap with next column
        if (resizedCol.w > newW) {
          this.moveToLeft(resizedCol.x + resizedCol.w, resizedCol.y);
        }
      },
      moveToRight(x, y, w) {
        const col = this.atPosition(x, y);
        if (!col) {
          return;
        }

        // Move next col first to make space
        const nextX = col.x + col.w;

        if (nextX === this.size) {
          // At the end of the row? Shift to the start of the next row
          this.moveToRight(0, y + 1, col.w);
          col.x = 0;
          col.y += 1;
        } else {
          this.moveToRight(nextX, y, w);
          col.x += w || 1;
          col.y = y;
        }
      },
      moveToLeft(x, y) {
        const col = this.atPosition(x, y);
        if (col) {
          this.moveToLeft(col.x + col.w, y);
          col.x -= 1;
        }
      },
      atPosition(x, y, layout) {
        const columns = layout || this.columns;

        return columns.find(col => {
          const inRow = y === col.y || (y >= col.y && y <= col.y + col.h - 1);
          return inRow && (x === col.x || (x >= col.x && x <= col.x + col.w - 1));
        });
      },
      changeSize(i, newH, newW) {
        const col = this.getColumnByIndex(i);
        col.h = 1;
        col.element.options.size = newW;
      },
      reorder(newLayout) {
        let free = false;
        let space = false;

        for (let y = 0; y <= this.maxRow; y += 1) {
          let x = 0;

          do {
            const occupied = this.atPosition(x, y, newLayout);

            if (occupied && space && free !== false) {
              // Set column into space of last row
              if (space >= occupied.w ) {
                occupied.y = y - 1;
                occupied.x = free;
                x = free + space;
                y -= 1;
              } else {
                // Not enough space -> reset position
                x = 0;
              }
              free = false;
              space = false;
            } else if (occupied && free !== false) {
              // Set column on free position
              occupied.x = free;
              free = false;
              x = occupied.x + occupied.w;
            } else if (occupied) {
              // Go further in grid
              x += occupied.w;
            } else if (!occupied && free === false) {
              // Set free position
              free = x;
              x += 1;
            } else {
              // There is a free position already, so just go further
              x += 1;
            }
          } while (x < this.size);

          // Set available space for column in next row
          if (free !== false) {
            space = this.size - free;
          } else {
            space = false;
          }
        }
      },
    },
  };
</script>
