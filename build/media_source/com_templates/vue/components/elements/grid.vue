<template>
	<grid-layout
		:layout="allColumns"
		:col-num="size"
		:is-draggable="true"
		:is-resizable="true"
		:vertical-compact="false"
		@layout-updated="reorder"
	>

		<!-- Columns -->
		<grid-item v-for="column in columns" :key="column.i"
			:class="['col-wrapper', column.element.type, column.element.options.class]"
			:i="column.i"
			:w="column.w"
			:h="1"
			:x="column.x"
			:y="column.y"
			@resize="resize"
			@resized="changeSize">

			<item :item="column.element"
					@delete="deleteElement({element: column.element, parent: grid})"
					@edit="editElement({element: column.element, parent: grid})">
			</item>
		</grid-item>

		<!-- Offsets -->
		<grid-item v-for="off in offsets" :key="off.i"
				:static="true"
				:class="['col-wrapper', 'col-offset']"
				:i="off.i"
				:w="off.w"
				:h="off.h"
				:x="off.column.x - off.w"
				:y="off.column.y">
			<div class="desc">
				{{ translate('COM_TEMPLATES_OFFSET') + ' ' + off.w }}
			</div>
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
      allColumns() {
        return this.columns.concat(this.offsets);
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
        offsets: [],
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
      this.initOffsets();
    },
    methods: {
      ...mapMutations([
        'deleteElement',
        'editElement',
        'setParent',
        'updateChildrenOrder',
        'updateSize'
      ]),
      mapGrid() {
        let x = 0;
        let y = 0;

        this.grid.children.forEach((child) => {
          // Takes care of elements other than 'column'
          const colSize = child.type !== 'column' ? this.size : child.options.size || 1;

          if (x + colSize > this.size) {
            x = 0;
            y += 1;
          }

          const col = {
            i: this.nextIndex,
            w: colSize,
            h: 1,
            x: x,
            y: y,
            element: child,
          };

          x += col.w;
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
            this.reorder();
          }
        });
        // Search for new children and offsets
        this.grid.children.forEach(child => {
          const found = this.columns.find(col => col.element === child);
          if (found && child.options.offset.lg && !found.offset) {
            this.createOffset(found);
            this.reorder();
          } else if (found && !child.options.offset.lg && found.offset) {
            this.removeOffset(found);
            this.reorder();
          }
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
      initOffsets() {
        this.columns.forEach(col => {
          // TODO: take current device offset (not just 'lg')
          if (col.element.options.offset.lg) {
            this.createOffset(col);
          }
        });
      },
      createOffset(column) {
        const offset = column.element.options.offset.lg;
        this.moveToRight(column.x, column.y, offset);

        const offsetObj = {
          i: `offset-${column.i}`,
          x: column.x - offset,
          y: column.y,
          w: offset,
          h: 1,
          column: column,
        };

        column.offset = offsetObj;
        this.offsets.push(offsetObj);
      },
      removeOffset(column) {
        const offset = column.offset;
        this.offsets.splice(this.columns.indexOf(offset), 1);
        delete column.offset;
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
        this.updateSize({element: col.element, size: newW});
      },
      reorder() {
        let free = false;
        let space = false;
        const reorderedColumns = [];

        for (let y = 0; y <= this.maxRow; y += 1) {
          let x = 0;

          do {
            const occupied = this.atPosition(x, y);
            let offsetW = 0;
            let fullW = occupied ? occupied.w : 1;

            // Mind the offset
            if (occupied && occupied.offset) {
              fullW += occupied.offset.w;
              offsetW = occupied.offset.w;
            }

            // Set new order into store too
            if (occupied && occupied.element) {
              reorderedColumns.push(occupied.element);
            }

            if (occupied && space) {
              // Set column into space of last row
              if (space >= fullW) {
                occupied.y = y - 1;
                occupied.x = this.size - space + offsetW;
                x = occupied.x + fullW;
                y -= 1;
              } else {
                // Not enough space -> reset position
                x = 0;
              }
              space = false;
            } else if (occupied && free !== false) {
              // Set column on free position
              occupied.x = free + offsetW;
              free = false;
              x = occupied.x + occupied.w;
            } else if (occupied) {
              // Go further in grid
              if (occupied.offset) {
                occupied.x += offsetW;
              }
              x += fullW;
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
            free = false;
          } else {
            space = false;
          }
        }

        this.updateChildrenOrder({parent: this.grid, children: reorderedColumns});
      },
    },
  };
</script>
