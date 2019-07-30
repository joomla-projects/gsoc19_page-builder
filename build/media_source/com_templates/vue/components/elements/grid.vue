<template>
	<grid-layout :layout="allItems"
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
			@move="move"
			@resize="resize"
			@resized="changeSize">
			<item :item="column.element" @delete="deleteElement({element: column.element, parent: grid})" @edit="editElement({element: column.element, parent: grid})"></item>
		</grid-item>

		<!-- Button to add new elements into the grid -->
		<grid-item :static="true"
			:i="addElementBtn.i"
			:w="addElementBtn.w"
			:h="addElementBtn.h"
			:x="addBtnPosition.x"
			:y="addBtnPosition.y">

			<button @click="addColumn()" class="column-btn btn btn-sm btn-success" type="button">
				<span class="icon-new"></span>
				<span>{{ translate('COM_TEMPLATES_ADD_COLUMN') }}</span>
			</button>

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
      allItems() {
        return this.columns.concat(this.addElementBtn);
      },
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
        let x = 0, y = 0, occupied = this.atPosition(x, y);

        while (occupied) {
          x += occupied.w;

          if (x === this.size) {
            y += 1;
            x = 0;
          }

          occupied = this.atPosition(x, y);
        }

        // Get available width to fill row
        let w = 1;
        let position = x + w;
        occupied = this.atPosition(position, y);

        while (!occupied && position < this.size) {
          w += 1;
          position += 1;
          occupied = this.atPosition(position, y);
        }

        return {x: x, y: y, w: w};
      },
      addBtnPosition() {
        let lastX = 0;
        let maxY = 0;
        this.columns.forEach(col => {
          maxY = Math.max(maxY, col.y, col.y + col.h - 1);
        });

        // Get last free position on last row
        let x;
        for (x = 0; x < this.size; x += 1) {
          if (this.atPosition(x, maxY)) {
            lastX = x + 1;
          }
        }

        if (lastX === this.size) {
          lastX = 0;
          maxY += 1;
        }

        return {x: lastX, y: maxY};
      },
      maxRow() {
        let maxY = 0;
        this.columns.forEach(col => maxY = Math.max(col.y, maxY));
        return maxY;
      },
    },
    data() {
      return {
        addElementBtn: {
          i: 'add',
          w: 1,
          h: 1,
          // Default x and y to set button into layout
          x: 0,
          y: 1,
        },
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
      addColumn() {
        // TODO: make type of new column selectable
        this.setParent(this.grid.children);
        this.addElement({
          name: 'column',
          config: ''
        });
      },
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
      move(i, newX, newY) {
        const movedChild = this.getColumnByIndex(i);

        // Col moves right
        if (movedChild.x < newX) {
          this.moveToRight(movedChild.x, movedChild.y);
        }

        // Col moves left
        if (movedChild.x > newX) {
          this.moveToLeft(movedChild.x, movedChild.y);
        }
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
      atPosition(x, y) {
        return this.columns.find(col => {
          const inRow = y === col.y || (y >= col.y && y <= col.y + col.h - 1);
          return inRow && (x === col.x || (x >= col.x && x <= col.x + col.w - 1));
        });
      },
      changeSize(i, newH, newW) {
        const col = this.getColumnByIndex(i);
        col.h = 1;
        col.element.options.size = newW;
      },
      reorder() {
        let free = false;
        let space = false;

        for (let y = 0; y <= this.maxRow; y += 1) {
          let x = 0;

          do {
            const occupied = this.atPosition(x, y);

            if (occupied && space >= occupied.w && free !== false) {
              // Set column into space of last row
              occupied.y = y - 1;
              occupied.x = free;
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
