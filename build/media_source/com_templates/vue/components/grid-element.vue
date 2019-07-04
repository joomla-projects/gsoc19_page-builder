<template>
	<grid-layout :layout="layout"
				 :col-num="gridSize"
				 :is-draggable="true"
				 :is-resizable="true"
	>
		<span class="grid-desc">{{ grid.type }} <span v-if="grid.options.class"><i>.{{grid.options.class}}</i></span></span>
		<div class="grid-btn-wrapper">
			<button type="button" class="btn btn-lg" @click="editElement(grid)">
				<span class="icon-options"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT') }}</span>
			</button>
			<button type="button" class="btn btn-lg" @click="deleteElement(grid)">
				<span class="icon-cancel"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_GRID') }}</span>
			</button>
		</div>

		<grid-item v-for="column in gridData.children" :key="column.i"
				   :class="['col-wrapper', column.type]"
				   :i="column.i"
				   :w="column.w"
				   :h="column.h"
				   :x="column.x"
				   :y="column.y"
				   @resized="changeSize"
		>
			<div class="btn-wrapper">
				<button type="button" class="btn btn-lg" @click="editElement(column)">
					<span class="icon-options"></span>
					<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
				</button>
				<button type="button" class="btn btn-lg" @click="deleteColumn(column)">
					<span class="icon-cancel"></span>
					<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_COLUMN') }}</span>
				</button>
			</div>

			<span class="desc">{{column.type}} <span v-if="column.options.class"><i>.{{column.options.class}}</i></span></span>
			<button v-if="childAllowed.includes(column.type)" type="button" class="btn btn-add btn-outline-info"
					@click="addElement(column)">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
			</button>

			<div v-for="child in column.children" :key="column.i" :class="['row-wrapper', child.type]">
				<grid-element :grid="child" :grid-size="child.options.gridSize || 12"></grid-element>
			</div>
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
  import VueGridLayout from 'vue-grid-layout';
  import {mapMutations, mapState} from 'vuex';

  export default {
    name: 'grid-element',
    props: {
      grid: {
        required: true,
        type: Object,
      },
      gridSize: {
        required: true,
        type: Number,
      },
    },
    computed: {
      ...mapState([
        'childAllowed'
      ]),
      layout: function () {
        return this.gridData.children.concat(this.addElementBtn);
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
        lastIndex: 0,
      };
    },
    components: {
      GridLayout: VueGridLayout.GridLayout,
      GridItem: VueGridLayout.GridItem,
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
      addColumn() {
        const defaultSize = 1;
        this.lastIndex += 1;

        this.gridData.children.push({
          type: 'Column',
          options: {
            size: defaultSize,
          },
          children: [],
          i: this.lastIndex,
          w: defaultSize,
          h: 1,
          x: this.nextFreePosition.x,
          y: this.nextFreePosition.y,
        });
      },
      mapGrid() {
        let x = 0;
        let y = 0;
        this.gridData.children.forEach((child, index) => {
          this.lastIndex = index;
          child.i = index;
          child.x = x;
          child.y = y;
          child.w = child.options.size || 1;
          child.h = child.options.height || 1;

          x += child.w;
          if (x >= this.gridSize) {
            x -= this.gridSize;
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
