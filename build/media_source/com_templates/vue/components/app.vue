<template>
	<div>
		<div id="sidebar" class="sidebar">
				<div class="sidebar-content">
				<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
				<button type="button" class="btn btn-lg closebtn" @click="closeNav()">
					<span class="icon-cancel"></span>
				</button>

				<component :is="selectedSettings"></component>
			</div>
		</div>

		<div class="pagebuilder" id="pagebuilder">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>

			<!-- Element -->
      <draggable v-model="elementArray" handle=".handle">
        <item v-for="element in elementArray" :key="element.key" :class="['row-wrapper']"
            :item="element" @delete="deleteElement({ element })" @edit="editElement({ element })"></item>
      </draggable>
			<!-- Element Ends -->

			<button @click="addElement()" class="btn btn-success btn-block" type="button">
				<span class="icon-new"></span>
				<span>{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}</span>
			</button>

			<!-- Modals -->
			<add-element-modal id="add-element"></add-element-modal>
		</div>
	</div>
</template>

<script>
  import {mapMutations, mapState} from 'vuex';
  import draggable from 'vuedraggable'

  export default {
    computed: {
      ...mapState([
        'selectedSettings'
      ]),
      elementArray: {
        get() {
          return this.$store.state.elementArray;
        },
        set(value) {
          this.updateElementArray(value);
        }
      }
    },
    watch: {
      elementArray: {
        handler(newVal) {
          document.getElementById('jform_params_grid').value = JSON.stringify(newVal);
        },
        deep: true,
      },
    },
    created() {
      this.mapElements(JSON.parse(document.getElementById('jform_params_grid').value));
      this.ifChildAllowed();
    },
    methods: {
      ...mapMutations([
        'ifChildAllowed',
        'mapElements',
        'closeNav',
        'deleteElement',
        'setParent',
        'updateElementArray',
        'editElement'
      ]),
      addElement() {
        this.setParent(this.elementArray);
        this.$modal.show('add-element');
      },
    },
    components: {
      draggable
    }
  };
</script>
