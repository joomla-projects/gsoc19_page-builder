<template>
	<div>
		<div id="sidebar" class="sidebar">
			<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
			<button type="button" class="btn btn-lg closebtn" @click="closeNav()">
				<span class="icon-cancel"></span>
			</button>
			<!-- TODO Add to store -->
			<!-- <component :is="selectedSettings" class="form-group" :grid='grid_selected' :column='column_selected' @reset="reset"></component> -->
		</div>

		<div class="pagebuilder" id="pagebuilder">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
			<form name="global-settings">
				<div class="form-group">
					<label for="grid-size">{{ translate('COM_TEMPLATES_GRID_SIZE') }}</label>
					<input v-model.number="gridSize" type="number" id="grid-size" name="grid-size" class="form-control">
				</div>
			</form>

			<div v-for="element in elementArray" :key="grid.id" :class="['row-wrapper', element.type]">
				<span>{{ element.type }} <span v-if="element.options.class"><i>.{{grid.options.class}}</i></span></span>
				<grid-element :grid="element" :grid-size="gridSize"
							  @editColumn="editColumn"
							  @addElement="addElement"
				>
				</grid-element>

				<div class="btn-wrapper">
					<button type="button" class="btn btn-lg" @click="editElement(element)">
						<span class="icon-options"></span>
						<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT') }}</span>
					</button>
					<button type="button" class="btn btn-lg" @click="deleteElement(element)">
						<span class="icon-cancel"></span>
						<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_GRID') }}</span>
					</button>
				</div>
			</div>

			<button type="button" v-if="childAllowed.includes(element.type)" class="btn btn-add btn-outline-info btn-block" @click="addElement(element)">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
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
    data() {
      return {
        gridSize: 12, // TODO: save and load into grid param
      };
    },
    computed: {
      ...mapState([
        'elementArray',
        'childAllowed',
        'parent',
        'allowedChildren'
      ]),
    },
    watch: {
      elementArray: {
        handler: function (newVal) {
          document.getElementById('jform_params_grid').value = JSON.stringify(newVal);
        },
        deep: true,
      },
      gridSize: {
        handler: function () {
          this.updateGridBackground();
        }
      },
    },
    components: {
      draggable
    },
    created() {
      this.mapGrid((JSON.parse(document.getElementById('jform_params_grid').value)));
      this.ifChildAllowed();
    },
    methods: {
      ...mapMutations([
        'ifChildAllowed',
        'addGrid',
        'deleteElement',
        'addColumn',
        'editColumn',
        'editElement',
        'addContainer',
        'fillAllowedChildren',
        'mapGrid',
        'closeNav'
      ]),
      addElement(parent) {
        this.fillAllowedChildren(parent.type);
        this.$store.commit('addElement', parent);
        this.$modal.show('add-element');
      },
      updateGridBackground() {
        const rows = document.querySelectorAll('.pagebuilder .row-wrapper');
        Array.prototype.forEach.call(rows, row => {
          const percentageWidth = (1 / this.gridSize) * 100;
          const pixelWidth = (row.getBoundingClientRect().width / 100) * percentageWidth;
          row.style.backgroundSize = `${pixelWidth}px 150px`;
        });
      },
    }
  };
</script>
