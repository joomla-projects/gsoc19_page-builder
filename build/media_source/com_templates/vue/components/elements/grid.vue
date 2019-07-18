<template>
	<grid-layout :layout="allItems"
		:col-num="gridSize"
		:is-draggable="true"
		:is-resizable="true">

		<!-- Columns -->
		<grid-item v-for="column in columns" :key="column.i"
			:class="['col-wrapper', column.element.type, column.element.options.offsetClass, column.element.options.class]"
			:i="column.i"
			:w="column.w"
			:h="column.h"
			:x="column.x"
			:y="column.y"
			@move="move"
			@resize="resize"
			@resized="changeSize">
			<item :item="column.element" @delete="deleteColumn(column)"></item>
		</grid-item>

		<!-- Button to add new elements into the grid -->
		<grid-item :static="true"
			:i="addElementBtn.i"
			:w="addElementBtn.w"
			:h="addElementBtn.h"
			:x="addBtnPosition.x"
			:y="addBtnPosition.y">

			<button @click="addColumn(grid)" class="column-btn btn btn-sm btn-success" type="button">
				<span class="icon-new"></span>
				<span>{{ translate('COM_TEMPLATES_ADD_COLUMN') }}</span>
			</button>

		</grid-item>

	</grid-layout>
</template>

<script>
  import {mapMutations, mapState} from 'vuex';

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
          type: 'column',
          options: {
            size: 1,
            height: 1,
            class: '',
            offset: ''
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
            w: child.type !== 'column' ? 12 : child.options.size || 1, // Takes care of elements other than 'column'
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
      getColumnByIndex(i) {
        return this.columns.find(col => col.i === i);
      },
      move(i, newX, newY) {
        const movedChild = this.getColumnByIndex(i);

        // Col moves right
        if (movedChild.x < newX) {
          this.moveToRight(movedChild.x, movedChild.y, movedChild.w);
        }
      },
      resize(i, newH, newW) {
        const resizedCol = this.getColumnByIndex(i);

        // Col gets more widely: Move following columns away
        if (resizedCol.w < newW) {
          this.moveToRight(resizedCol.x + resizedCol.w, resizedCol.y);
          return;
        }

        // Col gets narrower: Fill gap with next column
        const nextCol = this.atPosition(resizedCol.x + resizedCol.w, resizedCol.y);
        if (resizedCol.w > newW && nextCol) {
          nextCol.x -= 1;
        }
      },
      moveToRight(x, y, w) {
        const col = this.atPosition(x, y);
        if (!col) {
          return;
        }

        const nextX = col.x + col.w;

        // At the end of the row? Shift to the start of the next row
        if (nextX === this.gridSize) {
          this.moveToRight(0, y + 1, col.w);
          col.x = 0;
          col.y += 1;
          return;
        }

        // Move next col to make space
        this.moveToRight(nextX, y, col.w);
        col.x += w || 1;
      },
      atPosition(x, y) {
        return this.columns.find(col => {
          const inRow = y === col.y || (y >= col.y && y <= col.y + col.h - 1);
          return inRow && (x === col.x || (x >= col.x && x <= col.x + col.w - 1));
        });
      },
      changeSize(i, newH, newW) {
        const col = this.getColumnByIndex(i);
        col.element.options.size = newW;
        col.element.options.height = newH;
      },
    },
  };
</script>
