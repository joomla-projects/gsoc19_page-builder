<template>
	<modal name="add-grid" role="dialog" :classes="['modal-content', 'v--modal']" height="auto">
		<div class="modal-header">
			<h5 class="modal-title">{{ translate('COM_TEMPLATES_SELECT_LAYOUT') }}</h5>
			<button @click="close" type="button" class="close" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<h6>{{ translate('COM_TEMPLATES_PREDEFINED') }}</h6>
			<div class="row image-selection">
				<div class="col icon" v-html="images.row12" @click="$emit('selection', [12])"></div>
				<div class="col icon" v-html="images.row66" @click="$emit('selection', [6, 6])"></div>
				<div class="col icon" v-html="images.row48" @click="$emit('selection', [4, 8])"></div>
				<div class="col icon" v-html="images.row84" @click="$emit('selection', [8, 4])"></div>
				<div class="col icon" v-html="images.row3333" @click="$emit('selection', [3, 3, 3, 3])"></div>
				<div class="col icon" v-html="images.row444" @click="$emit('selection', [4, 4, 4])"></div>
				<div class="col icon" v-html="images.row363" @click="$emit('selection', [3, 6, 3])"></div>
			</div>
		</div>
	</modal>
</template>

<script>
  export default {
    name: 'modal-add-grid',
    props: {
      joptions: {
        type: Object,
        required: false,
        default: function () {
          return window.Joomla.getOptions('com_templates');
        },
      },
      images: {
        type: Object,
        required: false,
        default: function () {
          return this.joptions.images;
        },
      },
    },
    methods: {
      close() {
        this.$modal.hide('add-grid');
      }
    },
  };
</script>
