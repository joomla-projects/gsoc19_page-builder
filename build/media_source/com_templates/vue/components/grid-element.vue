<template>
	<grid-layout class="row-wrapper"
				 :layout="grid.children"
				 :col-num="gridSize"
				 :is-draggable="true"
				 :is-resizable="false"
	>
		<!-- TODO: find a better way to show these buttons -->
		<div class="btn-wrapper">
			<button type="button" class="btn btn-lg" @click="$emit('editGrid', grid)">
				<span class="icon-options"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_GRID') }}</span>
			</button>
			<button type="button" class="btn btn-lg" @click="$emit('deleteGrid', grid)">
				<span class="icon-cancel"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_GRID') }}</span>
			</button>
		</div>

		<grid-item v-for="column in grid.children" :key="column.i" class="col-wrapper"
				   :is-resizable="true"
				   :i="column.i"
				   :w="column.w"
				   :h="column.h"
				   :x="column.x"
				   :y="column.y"
		>
			<div class="btn-wrapper">
				<button type="button" class="btn btn-lg" @click="$emit('editColumn')">
					<span class="icon-options"></span>
					<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
				</button>
				<button type="button" class="btn btn-lg" @click="$emit('deleteColumn')">
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
		<grid-item
				   :i="addElementIndex"
				   :is-draggable="false"
				   :static="true"
				   :w="1"
				   :h="1"
				   :x="nextFreePosition.x"
				   :y="nextFreePosition.y"
		>
			<button class="btn btn-outline-info" type="button" @click="$emit('addColumn', grid)">
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
      }
    },
    computed: {
	  nextFreePosition: function() {
        let nextPosition = 0;
        let rowNumber = 0;

        this.grid.children.forEach(child => {
          nextPosition += child.w;

          if (nextPosition === this.gridSize) {
            nextPosition = 0;
            rowNumber += 1;
          }
        });
        return {x: nextPosition, y: rowNumber};
	  }
    },
    data() {
      return {
        addElementIndex: 'add',
      };
    },
    components: {
      GridLayout: VueGridLayout.GridLayout,
      GridItem: VueGridLayout.GridItem,
    },
  };
</script>
