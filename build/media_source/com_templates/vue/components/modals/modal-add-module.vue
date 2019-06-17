<template>
	<modal name="add-module" role="dialog" :classes="['modal-content', 'v--modal']" height="auto">
		<div class="modal-header">
			<h3 class="modal-title">{{ translate('COM_TEMPLATES_SELECT_MODULE') }}</h3>
			<button @click="close" type="button" class="close" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body"></div>
	</modal>
</template>

<script>
  export default {
    name: 'modal-add-module',
    props: {},
    methods: {
      close() {
        this.$modal.hide('add-module');
      }
    },
  };
</script>
