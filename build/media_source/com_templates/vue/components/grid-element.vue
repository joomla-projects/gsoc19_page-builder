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
				   @resized="changeSize"
		>

			<div class="btn-wrapper">
				<button type="button" class="btn btn-lg" @click="editElement(column.element)">
					<span class="icon-options"></span>
					<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
				</button>
				<button type="button" class="btn btn-lg" @click="deleteElement(column.element)">
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
				   :x="this.lastPosition.x"
				   :y="this.lastPosition.y"
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
      allItems: function () {
        return this.columns.concat(this.addElementBtn);
      },
      nextFreePosition: function () {
        let nextPosition = 0;
        let rowNumber = 0;
        let occupied = this.gridData.children.find(child => child.x === nextPosition && child.y === rowNumber);

        while (occupied) {
          nextPosition += occupied.w;

          if (nextPosition >= this.gridSize) {
            rowNumber += 1;
            nextPosition -= this.gridSize;
          }

          occupied = this.gridData.children.find(child => {
            // Check directly occupied positions or elements higher than one row
            return child.x === nextPosition && (rowNumber === child.y || rowNumber <= child.y + child.h - 1);
          });
        }

        return {x: nextPosition, y: rowNumber};
      },
      lastPosition: function () {
        let lastChild;
        let lastX = 0;
        let lastY = 0;

        // Get Y
        this.gridData.children.forEach(child => {
          lastY = Math.max(lastY, child.y);

          if (child.y === lastY && child.h > 1) {
            lastY += child.h;
          }
        });

        // Get X
        const inLastRow = this.gridData.children.filter(child => {
          return lastY === child.y || lastY === child.y + child.h - 1;
        });

        inLastRow.forEach(child => {
          lastX = Math.max(lastX, child.x);
          if (child.x === lastX) {
            lastChild = child;
          }
        });

        lastX += lastChild ? lastChild.w : 0;

        if (lastX >= this.gridSize) {
          lastX -= this.gridSize;
          lastY += 1;
        }

        return {x: lastX, y: lastY};
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

        this.columns.push({
          i: this.nextIndex,
          w: this.nextSpace.w,
          h: 1,
          x: this.nextFreePosition.x,
          y: this.nextFreePosition.y,
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
            w: child.options.size || 1,
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
        });
      },
      changeSize(i, newH, newW) {
        const child = this.gridData.children.find(child => child.i === i);
        child.options.size = newW;
        child.options.height = newH;
      },
    }
  };
</script>
