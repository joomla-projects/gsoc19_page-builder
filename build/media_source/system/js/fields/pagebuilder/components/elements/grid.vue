<template>
	<grid-layout
		ref="gridLayout"
		:layout="allColumns"
		:col-num="size"
		:is-draggable="true"
		:is-resizable="true"
		:vertical-compact="false"
		@layout-updated="updateLayout"
	>

		<!-- Columns -->
		<grid-item
			v-for="column in columns"
			ref="gridItems"
			:key="column.i"
			:class="['col-wrapper', column.element.type, column.element.options.class]"
			:i="column.i"
			:w="column.w"
			:h="1"
			:x="column.x"
			:y="column.y"
			@resize="resize"
			@resized="changeSize"
			@moved="changePosition"
		>

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
				{{ translate('JLIB_PAGEBUILDER_OFFSET') + ' ' + off.device + '-' + off.w }}
			</div>
		</grid-item>

		<!-- Always creates a row at the end to ensure a correct layout -->
		<grid-item
				:static="true"
				class="col-invisible"
				:i="rowPlaceholder.i"
				:w="rowPlaceholder.w"
				:h="rowPlaceholder.h"
				:x="size - 1"
				:y="lastRow"
		>
		</grid-item>
	</grid-layout>
</template>

<script>
  import {mapGetters, mapMutations, mapState} from 'vuex';

  export default {
    name: 'grid',
    props: {
      grid: {
        required: true,
        type: Object,
      },
    },
    computed: {
      ...mapState([
        'activeDevice',
        'size'
      ]),
      ...mapGetters([
        'getElementSize'
      ]),
      allColumns() {
        return this.columns.concat(this.offsets).concat(this.rowPlaceholder);
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
      lastSpace() {
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
      lastRow() {
        const x = this.size;
        let y = this.maxRow;

        const col = this.columns.find(col => col.y === y && col.x + col.w === x);
        if (col) {
          y += 1;
        }

        return y;
      },
      columnWidth() {
        return this.$refs.gridLayout.width / this.size;
      },
    },
    data() {
      return {
        columns: [],
        moved: false,
        offsets: [],
        rowPlaceholder: {
          i: 'rowPlaceholder',
          x: 11,
          y: 1,
          w: 1,
          h: 1,
        },
        reorderedColumns: [],
        resizeStartX: 0,
      };
    },
    watch: {
      grid: {
        deep: true,
        handler() {
          this.mapElementChanges();
        }
      },
      activeDevice: {
        handler() {
          this.updateSizes();
          this.updateLayout();
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
        'updateChildrenOrder'
      ]),
      mapGrid() {
        let x = 0;
        let y = 0;

        this.grid.children.forEach((child) => {
          // Takes care of elements other than 'column'
          const colSize = this.getElementSize(child);

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
        let changes = false;

        // Search for removed children
        this.columns.forEach(col => {
          const found = this.grid.children.indexOf(col.element);
          if (found === -1) {
            this.columns.splice(this.columns.indexOf(col), 1);
            // Following columns fill the created gap
            this.moveToLeft(col.x + col.w, col.y, col.w);
            changes = true;
          }
        });

        // Search for new children
        this.grid.children.forEach(child => {
          const found = this.columns.find(col => col.element === child);
          if (!found) {
            const newPosition = this.lastSpace;
            this.columns.push({
              i: this.nextIndex,
              w: newPosition.w,
              h: 1,
              x: newPosition.x,
              y: newPosition.y,
              element: child,
            });
            child.options.size[this.activeDevice] = newPosition.w;
            changes = true;
          }
        });

        if (changes) {
          this.updateLayout();
        }
      },
      initOffsets() {
        this.columns.forEach(col => {
          if (col.element.options.offset[this.activeDevice]) {
            this.createOffset(col);
          }
        });
      },
      resetOffsets() {
        this.columns.forEach(col => {
          if (col.offset) {
            this.removeOffset(col);
          }
        });
        this.offsets = [];
      },
      createOffset(column) {
        const offset = column.element.options.offset[this.activeDevice];
        this.moveToRight(column.x, column.y, offset);

        // Be sure that there is space for offset
        if (column.x === 0) {
          this.moveToRight(column.x, column.y, offset);
        }

        const offsetObj = {
          i: `offset-${column.element.key}`,
          x: column.x - offset,
          y: column.y,
          w: offset,
          h: 1,
          column: column,
          device: this.activeDevice,
        };

        column.offset = offsetObj;
        this.offsets.push(offsetObj);
      },
      removeOffset(column) {
        const offset = column.offset;
        delete column.offset;
        this.moveToLeft(column.x, column.y, offset.w);
      },
      updateSizes() {
        this.columns.forEach(col => {
          const activeSize = this.getElementSize(col.element);
          if (col.w < activeSize) {
            this.moveToRight(col.x + col.w, col.y, activeSize - col.w);
            col.w = activeSize;
          } else if (col.w > activeSize) {
            this.moveToLeft(col.x + col.w, col.y, col.w - activeSize);
            col.w = activeSize;
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

        if (nextX === this.size || nextX + w > this.size) {
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
      moveToLeft(x, y, w) {
        const col = this.atPosition(x, y);
        if (col && col.x > 0) {
          this.moveToLeft(col.x + col.w, y, w);
          col.x -= w || 1;
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
        col.element.options.size[this.activeDevice] = newW;
      },
      changePosition() {
        this.moved = true;
      },
      updateLayout() {
        this.resetOffsets();
        this.positioning();
        this.initOffsets();
        this.setResizeListeners();

        if (this.moved) {
          const colElements = [];
          this.columns.forEach(col => colElements.push(col.element));
          this.updateChildrenOrder({parent: this.grid, children: colElements});
          this.moved = false;
        }
      },
      positioning() {
        // Bring columns in correct order to fill gaps one after another
        this.columns.sort((col1, col2) => {
          if (col1.y < col2.y || (col1.y === col2.y && col1.x < col2.x)) {
            return -1;
          }
          if (col1.y > col2.y || (col1.y === col2.y && col1.x > col2.x)) {
            return 1;
          }
          return 0;
        });

        for (const col of this.columns) {
          const freePosition = this.getGap(col.x, col.y, col.w);
          if (freePosition) {
            col.x = freePosition.x;
            col.y = freePosition.y;
          }
        }
      },
      getGap(colX, colY, colW) {
        let freeX = false;
        let freeY = false;
        let space = false;
        let stop = false;

        for (let y = colY; y >= 0; y -= 1) {
          if (stop) {
            break;
          }

          // First x is not higher than column.x, in other rows the x just gives enough width
          const maxX = y === colY ? colX - 1 : this.size - colW;
          for (let x = maxX; x >= 0; x -= 1) {
            if (this.atPosition(x, y)) {
              stop = true;
              break;
            } else {
              freeX = x;
              freeY = y;
              space = true;
            }
          }
        }

        return space ? {x: freeX, y: freeY} : false;
      },
      setResizeListeners() {
        this.$refs.gridItems.forEach((item) => {
          item.$refs.handle.addEventListener('mousedown', (event) => this.handleResizeMouseDown(event, item));
          item.$refs.handle.addEventListener('mouseup', (event) => this.handleResizeMouseUp(event, item));
        });
      },
      handleResizeMouseDown(event, item) {
        // Is item at the end of the row?
        if (item.innerX + item.innerW === this.size && item.innerW !== this.size) {
          this.resizeStartX = event.x;
        }
      },
      handleResizeMouseUp(event, item) {
        const dragDistance = event.x - this.resizeStartX;
        if (this.resizeStartX && dragDistance > this.columnWidth) {
          const addWidth = Math.round(dragDistance / this.columnWidth);
          if (addWidth) {
            const column = this.getColumnByIndex(item.i);
            this.moveToRight(0, column.y + 1, column.w + addWidth);
            column.x = 0;
            column.y += 1;
            column.w += addWidth;
            column.element.options.size[this.activeDevice] = column.w;
          }
        }
        this.resizeStartX = 0;
      },
    },
  };
</script>
