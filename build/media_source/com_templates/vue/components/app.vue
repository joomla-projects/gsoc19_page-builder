<template>
	<div>
		<div id="sidebar" class="sidebar">
			<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
			<button type="button" class="btn btn-lg closebtn" @click="closeNav()">
				<span class="icon-cancel"></span>
			</button>

			<component :is="selectedSettings" class="form-group"></component>
		</div>

		<div class="pagebuilder" id="pagebuilder">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
			<form name="global-settings">
				<div class="form-group">
					<label for="grid-size">{{ translate('COM_TEMPLATES_GRID_SIZE') }}</label>
					<input v-model.number="gridSize" type="number" id="grid-size" name="grid-size" class="form-control">
				</div>
			</form>

			<!-- TODO: make the rows sortable again ('draggable' breaks resizable elements) -->
			<!-- Element -->
			<item v-for="element in elementArray" :key="element.id" :class="['row-wrapper']"
				  :item="element" @delete="deleteElement(element)"></item>
			<!-- Element Ends -->

			<button @click="addElement(elementArray)" class="btn btn-outline-info btn-block" type="button">
				<span class="icon-new"></span>
				<span>{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}</span>
			</button>

			<!-- Modals -->
			<add-element-modal id="add-element"></add-element-modal>
		</div>
	</div>
</template>

<script>
  import draggable from 'vuedraggable';
  import {mapMutations, mapState} from 'vuex';

  export default {
    computed: {
      ...mapState([
        'elementArray',
        'selectedSettings'
      ]),
      gridSize: {
        get() {
          return this.$store.state.gridSize;
        },
        set(value) {
          this.updateGridSize(value);
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
    components: {
      draggable
    },
    created() {
      this.mapElements(JSON.parse(document.getElementById('jform_params_grid').value));
      this.ifChildAllowed();
    },
    methods: {
      ...mapMutations([
        'ifChildAllowed',
        'editElement',
        'fillAllowedChildren',
        'mapElements',
        'closeNav',
        'updateGridSize'
      ]),
      addElement(parent) {
        this.fillAllowedChildren(parent.type);
        this.$store.commit('addElement', parent);
        this.$modal.show('add-element');
      },
      deleteElement(element) {
        this.$store.commit('deleteElement', { element });
      }
    }
  };
</script>
