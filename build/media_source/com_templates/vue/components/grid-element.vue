<template>
	<grid-layout :layout="gridData.children"
				 :col-num="gridSize"
				 :is-draggable="true"
				 :is-resizable="false"
	>
		<grid-item v-for="column in gridData.children" :key="column.i" class="col-wrapper"
				   :is-resizable="true"
				   :i="column.i"
				   :w="column.w"
				   :h="column.h"
				   :x="column.x"
				   :y="column.y"
		>
			<div class="btn-wrapper">
				<button type="button" class="btn btn-lg" @click="$emit('editColumn', column)">
					<span class="icon-options"></span>
					<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
				</button>
				<button type="button" class="btn btn-lg" @click="deleteColumn(column)">
					<span class="icon-cancel"></span>
					<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_COLUMN') }}</span>
				</button>
			</div>
			<span>{{column.w}} (<i>{{column.type}}<span
					v-if="column.options.class">, .{{column.options.class}}</span></i>)</span>
			<button type="button" class="btn btn-add btn-outline-info" @click="$emit('addElement', column)">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
			</button>
		</grid-item>

		<!-- Button to add new elements into the grid -->
		<grid-item :static="true"
				   :i="addElementIndex"
				   :w="1"
				   :h="1"
				   :x="nextFreePosition.x"
				   :y="nextFreePosition.y"
		>
			<button class="column-btn btn btn-outline-info" type="button" @click="addColumn">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_COLUMN') }}
			</button>
		</grid-item>
	</grid-layout>
</template>

<script>
  import VueGridLayout from 'vue-grid-layout';

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
      nextFreePosition: function () {
        let nextPosition = 0;
        let rowNumber = 0;

        this.gridData.children.forEach(child => {
          nextPosition += child.options.size;

          if (nextPosition >= this.gridSize) {
            nextPosition -= this.gridSize;
            rowNumber += 1;
          }
        });

        return {x: nextPosition, y: rowNumber};
      }
    },
    data() {
      return {
        addElementIndex: 'add',
        gridData: this.grid,
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
      addColumn() {
        const defaultSize = 1;

        this.gridData.children.push({
          type: 'column',
          options: {
            size: defaultSize,
          },
          children: [],
          i: this.gridData.children.length,
          w: defaultSize,
          h: 1,
          x: this.nextFreePosition.x,
          y: this.nextFreePosition.y,
        });
      },
      deleteColumn(column) {
        const index = this.gridData.children.indexOf(column);
        if (index > -1) {
          this.gridData.children.splice(index, 1);
        }
      },
      mapGrid() {
        let x = 0;
        let y = 0;
        this.gridData.children.forEach((child, index) => {
          child.i = index;
          child.x = x;
          child.y = y;
          child.w = child.options.size;
          child.h = 1;

          x += child.w;
          if (x >= this.gridSize) {
            x -= this.gridSize;
            y += 1;
		  }
        });
      },
    }
  };
</script>
