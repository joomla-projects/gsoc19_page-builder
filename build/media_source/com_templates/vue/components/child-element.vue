<template>
	<vue-draggable-resizable class-name="col-wrapper"
							 axis="x"
							 :parent="true"
							 :grid="[step,0]"
							 :handles="['ml','mr']"
							 :x="position"
							 :min-width="step"
							 :w="step"
							 :h="150"
							 @resizing="changeSize"
							 @resizestop="resizeEnd"
	>
		<div class="btn-wrapper">
			<button type="button" class="btn btn-lg" @click="$emit('edit')">
				<span class="icon-options"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
			</button>
			<button type="button" class="btn btn-lg" @click="$emit('remove')">
				<span class="icon-cancel"></span>
				<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_COLUMN') }}</span>
			</button>
		</div>
		<span>{{size}} (<i>{{element.type}}<span v-if="element.options.class">, .{{element.options.class}}</span></i>)</span>
		<button type="button" class="btn btn-add btn-outline-info" @click="$emit('add', element)">
			<span class="icon-new"></span>
			{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
		</button>
	</vue-draggable-resizable>
</template>

<script>
  import VueDraggableResizable from 'vue-draggable-resizable';

  export default {
    name: 'child-element',
    props: {
      element: {
        required: true,
        type: Object,
		default: function() {
		  return {
		    type: 'column',
		    options: {
		      class: '',
			  size: 1,
			  position: 0,
			}
		  }
		}
      },
      step: {
        required: true,
        type: Number,
      }
    },
    data: function () {
      return {
        width: this.step,
        size: this.element.options.size || 1,
      };
    },
    components: {
      VueDraggableResizable,
    },
	computed: {
      position: function() {
        const pos = this.element.options.position || 0;
        return pos * this.element.options.size;
	  }
	},
    methods: {
      resizeEnd(left, top, width) {
        this.width = width;
      },
      changeSize(left, top, width) {
        this.size = Math.round(width / this.step);
      }
    }
  };
</script>
