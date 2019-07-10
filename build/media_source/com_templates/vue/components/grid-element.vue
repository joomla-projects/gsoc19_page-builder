<template>
	<grid-layout :layout="allItems"
				 :col-num="gridSize"
				 :is-draggable="true"
				 :is-resizable="true"
	>

		<div class="grid-btn-wrapper btn-wrapper">
			<button type="button" class="btn btn-lg" @click="editElement(grid)">
				<span class="icon-options"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT') }}</span>
			</button>
			<button type="button" class="btn btn-lg" @click="deleteElement(grid)">
				<span class="icon-cancel"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_GRID') }}</span>
			</button>
		</div>

		<grid-item v-for="column in columns" :key="column.i"
				   :class="['col-wrapper', column.element.type]"
				   :i="column.i"
				   :w="column.w"
				   :h="column.h"
				   :x="column.x"
				   :y="column.y"
				   @move="move"
				   @resize="resize"
				   @resized="changeSize"
		>

			<div class="btn-wrapper">
				<button type="button" class="btn btn-lg" @click="editElement(column.element)">
					<span class="icon-options"></span>
					<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
				</button>
				<button type="button" class="btn btn-lg" @click="deleteColumn(column)">
					<span class="icon-cancel"></span>
					<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_COLUMN') }}</span>
				</button>
			</div>

			<span class="desc">{{column.element.type}}
				<span v-if="column.element.options.class"><i>.{{column.element.options.class}}</i></span>
			</span>
			<button v-if="childAllowed.includes(column.element.type)" type="button" class="btn btn-add btn-outline-info"
					@click="addElement(column.element)">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
			</button>

		</grid-item>

		<!-- Button to add new elements into the grid -->
		<grid-item :static="true"
				   :i="addElementBtn.i"
				   :w="addElementBtn.w"
				   :h="addElementBtn.h"
				   :x="addBtnPosition.x"
				   :y="addBtnPosition.y"
		>

			<button v-if="childAllowed.includes(grid.type)" class="column-btn btn btn-outline-info" type="button"
					@click="addColumn(grid)">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_COLUMN') }}
			</button>

		</grid-item>

	</grid-layout>
</template>

<script>
  import {mapMutations, mapState} from 'vuex';

  export default {
    name: 'grid-element',
    props: {
      grid: {
        required: true,
        type: Object,
      },
    },
    computed: {
      ...mapState([
        'childAllowed',
        'gridSize'
      ]),
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
        let x = 0;
        let y = 0;
        let occupied = this.atPosition(x, y);

        while (occupied) {
          x += occupied.w;

          if (x === this.gridSize) {
            y += 1;
            x = 0;
          }

          occupied = this.atPosition(x, y);
        }

        // Get available width to fill row
        let w = 1;
        let position = x + w;
        occupied = this.atPosition(position, y);

        while (!occupied && position < this.gridSize) {
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
        for (x = 0; x < this.gridSize; x += 1) {
          if (this.atPosition(x, maxY)) {
            lastX = x + 1;
          }
        }

        if (lastX === this.gridSize) {
          lastX = 0;
          maxY += 1;
        }

        return {x: lastX, y: maxY};
      }
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
        gridData: this.grid,
        columns: [],
      };
    },
    created() {
      this.mapGrid();
    },
    methods: {
      ...mapMutations([
        'deleteColumn',
        'deleteElement',
        'editElement',
        'fillAllowedChildren'
      ]),
      addElement(parent) {
        this.fillAllowedChildren(parent.type);
        this.$store.commit('addElement', parent);
        this.$modal.show('add-element');
      },
      deleteColumn(column) {
        this.gridData.children.splice(this.gridData.children.indexOf(column.element), 1);
      },
      addColumn() {
        // TODO: make type of new column selectable
        this.$store.commit('addElement', this.gridData);

        const newElement = {
          type: 'Column',
          options: {
            size: 1,
            height: 1,
          },
          children: [],
        };

        const newPosition = this.nextSpace;
        this.columns.push({
          i: this.nextIndex,
          w: newPosition.w,
          h: 1,
          x: newPosition.x,
          y: newPosition.y,
          element: newElement,
        });

        // Add column element to grid element too
        this.gridData.children.push(newElement);
      },
      mapGrid() {
        let x = 0;
        let y = 0;

        this.gridData.children.forEach((child) => {
          const col = {
            i: this.nextIndex,
            w: child.type !== 'Column' ? 12 : child.options.size || 1, // Takes care of elements other than 'Column'
            h: child.options.height || 1,
            x: x,
            y: y,
            element: child,
          };

          x += col.w;
          if (x === this.gridSize) {
            x = 0;
            y += 1;
          }
          this.columns.push(col);
        });
      },
      getColumnByIndex(i) {
        return this.columns.find(col => col.i === i);
      },
      move(i, newX, newY) {
        const movedChild = this.getColumnByIndex(i);

        // Col moves right
        if (movedChild.x < newX) {
          this.moveNextToRight(movedChild);
        }
      },
      resize(i, newH, newW) {
        const resizedCol = this.getColumnByIndex(i);

        // Col gets more widely
        if (resizedCol.w < newW) {
          this.moveNextToRight(resizedCol, true);
          return;
        }

        // Col gets narrower
        const nextCol = this.atPosition(resizedCol.x + resizedCol.w, resizedCol.y);
        if (resizedCol.w > newW && nextCol) {
          // Fill gap with next column
          nextCol.x -= 1;
          nextCol.w += 1;
        }
      },
      moveNextToRight(afterCol, withResizing) {
        const col = this.atPosition(afterCol.x + afterCol.w, afterCol.y);
        if (!col) {
          return;
        }

        // At the end of the row?
        if (col.x === this.gridSize - 1) {
          const newPos = this.nextSpace;
          col.x = newPos.x;
          col.y = newPos.y;
          col.w = newPos.w;
          return;
        }

        // Space available? Just move
        if (!this.atPosition(col.x + col.w, col.y) && col.x + col.w !== this.gridSize) {
          col.x += 1;
          return;
        }

        // Make col slimmer to let following columns on position
        if (col.w > 1 && withResizing) {
          col.w -= 1;
          col.x += 1;
        } else {
          // Move next col to make space for moving
          this.moveNextToRight(col);
          col.x += 1;
        }
      },
      atPosition(x, y) {
        return this.columns.find(col => {
          const inRow = y === col.y || y <= col.y + col.h - 1;
          return inRow && (x === col.x || (x >= col.x && x <= col.x + col.w - 1));
        });
      },
      changeSize(i, newH, newW) {
        const col = this.getColumnByIndex(i);
        col.element.options.size = newW;
        col.element.options.height = newH;
      },
      mapElementChanges() {
        // Search for new children
        this.gridData.children.forEach(child => {
          const found = this.columns.find(col => col.element === child);
          if (!found) {
            const newPosition = this.nextSpace;
            this.columns.push({
              i: this.nextIndex,
              w: child.options.size || newPosition.w,
              h: 1,
              x: newPosition.x,
              y: newPosition.y,
            });
          }
        });
        // Search for removed children
        this.columns.forEach(col => {
          const found = this.gridData.children.indexOf(col.element);
          if (found === -1) {
            // Remove column from grid
            this.columns.splice(this.columns.indexOf(col), 1);
          }
        });
      },
    },
    watch: {
      grid: {
        deep: true,
        handler() {
          this.mapElementChanges();
        }
      }
    }
  };
</script>
