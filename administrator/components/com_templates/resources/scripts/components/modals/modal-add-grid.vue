<template>
	<modal name="add-grid" role="dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{ translate('COM_TEMPLATES_SELECT_LAYOUT') }}</h5>
				<button @click="$modal.hide('add-grid')" type="button" class="close" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body row">
				<div class="col" v-html="images.row12" v-on:click="$emit('selection', [12])"></div>
				<div class="col" v-html="images.row66" v-on:click="$emit('selection', [6, 6])"></div>
				<div class="col" v-html="images.row48" v-on:click="$emit('selection', [4, 8])"></div>
				<div class="col" v-html="images.row84" v-on:click="$emit('selection', [8, 4])"></div>
				<div class="col" v-html="images.row3333" v-on:click="$emit('selection', [3, 3, 3, 3])"></div>
				<div class="col" v-html="images.row444" v-on:click="$emit('selection', [4, 4, 4])"></div>
				<div class="col" v-html="images.row363" v-on:click="$emit('selection', [3, 6, 3])"></div>
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
  };
</script>
