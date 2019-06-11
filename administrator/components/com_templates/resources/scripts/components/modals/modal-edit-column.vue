<template>
	<modal name="edit-column" role="dialog" :classes="['modal-content', 'v--modal']" @before-open="beforeOpen">
		<div class="modal-header">
			<h5 class="modal-title">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</h5>
			<button @click="close" type="button" class="close" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<fieldset>
				<label for="classes">{{ translate('COM_TEMPLATES_ADD_CLASS') }}</label>
				<input id="classes" name="classes" type="text" v-model="column.options.class">
			</fieldset>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-danger mr-auto" @click="reset">{{ translate('JSEARCH_RESET') }}</button>
			<button type="button" class="btn btn-success" @click="save">
				{{ translate('COM_TEMPLATES_SAVE') }}
			</button>
		</div>
	</modal>
</template>

<script>
  export default {
    name: 'modal-edit-column',
    props: {
      column: {
        options: {
          class: '',
        },
      },
    },
    data() {
      return {
        oldClasses: ''
      };
    },
    methods: {
      beforeOpen() {
        this.oldClasses = this.column.options.class;
      },
      save() {
        this.$modal.hide('edit-column');
      },
      close() {
        this.reset();
        this.$modal.hide('edit-column');
      },
      reset() {
        this.column.options.class = this.oldClasses;
      }
    }
  };
</script>
