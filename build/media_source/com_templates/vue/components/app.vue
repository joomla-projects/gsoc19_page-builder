<template>
	<div>
		<v-navigation-drawer v-model="selectedSettings" app disable-resize-watcher id="settings" class="settings">
			<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
			<component :is="selectedSettings" class="form-group" :grid='gridSelected' :column='columnSelected'
					   @reset="reset"></component>
		</v-navigation-drawer>

		<v-content class="pagebuilder">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
			<form name="global-settings">
				<div class="form-group">
					<label for="grid-size">{{ translate('COM_TEMPLATES_GRID_SIZE') }}</label>
					<input v-model.number="gridSize" type="number" id="grid-size" name="grid-size" class="form-control">
				</div>
			</form>

			<div v-for="grid in gridArray" :key="grid.id" class="row-wrapper">
				<span v-if="grid.options.class"><i>.{{grid.options.class}}</i></span>

				<grid-element :grid="grid" :grid-size="gridSize"
							  @editColumn="editColumn"
							  @addElement="addElement"
				>
				</grid-element>

				<div class="btn-wrapper">
					<button type="button" class="btn btn-lg" @click="editGrid(grid)">
						<span class="icon-options"></span>
						<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_GRID') }}</span>
					</button>
					<button type="button" class="btn btn-lg" @click="deleteGrid(grid)">
						<span class="icon-cancel"></span>
						<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_GRID') }}</span>
					</button>
				</div>
			</div>

			<button type="button" class="btn btn-outline-info btn-block" @click="show('add-grid')">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_GRID') }}
			</button>

			<!-- Modals -->
			<add-grid-modal id="add-grid" @selection="addGrid"></add-grid-modal>
			<add-element-modal id="add-element" :elements="elements" :column="columnSelected"></add-element-modal>
		</v-content>
	</div>
</template>

<script>
  import draggable from 'vuedraggable';

  export default {
    props: {
      grid: {
        default: function () {
          return JSON.parse(document.getElementById('jform_params_grid').value);
        },
      },
    },
    data() {
      return {
        gridSelected: '',
        columnSelected: '',
        gridArray: this.grid,
        selectedSettings: false,
        elements: window.Joomla.getOptions('com_templates').elements,
        gridSize: 12, // TODO: save and load into grid param
      };
    },
    mounted() {
      //this.updateGridBackground();
    },
    watch: {
      grid: {
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
    methods: {
      addGrid(sizes) {
        const newGrid = {
          type: 'grid',
          options: {
            class: '',
          },
          children: []
        };

        sizes.forEach(size => {
          newGrid.children.push({
            type: 'column',
            options: {
              size: size,
			  height: 1,
              class: '',
            },
            children: [],
          });
        });

        this.gridArray.push(newGrid);
        this.reset();
        this.hide('add-grid');
      },
      deleteGrid(grid) {
        const index = this.gridArray.indexOf(grid);
        if (index > -1) {
          this.gridArray.splice(index, 1);
        }
      },
      addColumn(grid) {
        this.gridSelected = grid;
        this.selectedSettings = 'add-column';
      },
      editPosition(grid, column) {
        this.gridSelected = grid;
        this.columnSelected = column;
        this.selectedSettings = 'edit-position';
      },
      editColumn(column) {
        this.columnSelected = column;
        this.selectedSettings = 'edit-column';
      },
      editGrid(grid) {
        this.gridSelected = grid;
        this.selectedSettings = 'edit-grid';
      },
      reset() {
        this.selectedSettings = false;
      },
      log(el) {
        console.log(el);
      },
      show(name) {
        this.$modal.show(name);
      },
      hide(name) {
        this.$modal.hide(name);
      },
      addElement(column) {
        this.columnSelected = column;
        this.show('add-element');
      },
      updateGridBackground() {
        const rows = document.querySelector('.pagebuilder').querySelectorAll('.row-wrapper');
        Array.prototype.forEach.call(rows, row => {
          row.style.backgroundSize = `${this.gridStep}px 150px`;
        });
      },
    }
  };
</script>
