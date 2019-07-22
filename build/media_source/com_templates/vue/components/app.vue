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

			<!-- TODO: make the rows sortable again ('draggable' breaks resizable elements) -->
			<!-- Element -->
			<item v-for="element in elementArray" :key="element.key" :class="['row-wrapper']"
				  :item="element" @delete="deleteElement(element)"></item>
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
    created() {
      this.mapElements(JSON.parse(document.getElementById('jform_params_grid').value));
      this.ifChildAllowed();
    },
    methods: {
      ...mapMutations([
        'ifChildAllowed',
        'mapElements',
        'closeNav',
        'updateGridSize'
      ]),
      addElement() {
        this.$store.commit('setParent', this.elementArray);
        this.$modal.show('add-element');
      },
      deleteElement(element) {
        this.$store.commit('deleteElement', { element });
      }
    }
  };
</script>
