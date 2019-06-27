<template>
	<div>
		<v-navigation-drawer v-model="showSettings" app id="Settings" class="settings">
			<h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
      <component :is="selectedSettings" class="form-group" :grid='grid_selected' :column='column_selected' @reset="reset"></component>
		</v-navigation-drawer>

		<v-content class="pagebuilder">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
			<!-- Element -->
			<draggable v-model="elementArray" ghost-class="drop">
				<div v-for="element in elementArray" class="row-wrapper">
                <span>{{ element.type }}</span>
					<div class="btn-wrapper">
						<button type="button" class="btn btn-lg" @click="editGrid(element)">
							<span class="icon-options"></span>
							<span class="sr-only">{{ translate('COM_TEMPLATES_EDIT') }}</span>
						</button>
						<button type="button" class="btn btn-lg" @click="deleteGrid(element)">
							<span class="icon-cancel"></span>
							<span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_GRID') }}</span>
						</button>
					</div>
					<span v-if="element.options.class != ''">.{{element.options.class}}</span>

                    <div v-if="element.type != 'Grid'">
                        <draggable v-model="element.children">
                            <div v-for="child in element.children" class="col-wrapper">
                                <span>{{ child.type }}</span>
                                <span v-if="child.options.class != ''">.{{ child.options.class }}</span>
                            </div>
                        </draggable>
                    </div>

					<!-- Column -->
                    <div v-if="element.type == 'Grid'">
                        <draggable v-model="element.children" class="row">
                        <div class="col-wrapper" v-for="column in element.children" :class="[column.options.size]">
                            <div class="btn-wrapper">
                            <button type="button" class="btn btn-lg" @click="editColumn(column)">
                                <span class="icon-options"></span>
                                <span class="sr-only">{{ translate('COM_TEMPLATES_EDIT_COLUMN') }}</span>
                            </button>
                            <button type="button" class="btn btn-lg" @click="deleteColumn(element,column)">
                                <span class="icon-cancel"></span>
                                <span class="sr-only">{{ translate('COM_TEMPLATES_DELETE_COLUMN') }}</span>
                            </button>
                            </div>

                            <span>{{column.options.size}} (<i>{{column.type}}<span v-if="column.options.class">, .{{column.options.class}}</span></i>)</span>
                            <!-- <button type="button" class="btn btn-add btn-outline-info" @click="addElement(column)">
                                <span class="icon-new"></span>
                                {{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
                            </button> -->
                        </div>
                        </draggable>

                        <button class="btn btn-add btn-outline-info" type="button" @click="addColumn(element)">
                        <span class="icon-new"></span>
                        {{ translate('COM_TEMPLATES_ADD_COLUMN') }}
                        </button>
                    </div>
                    <!-- Column Ends-->

                <button type="button" class="btn btn-add btn-outline-info btn-block" @click="addElement(element)">
                    <span class="icon-new"></span>
                    {{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
			    </button>
				</div>
			</draggable>
			<!-- Element Ends -->

			<button type="button" class="btn btn-add btn-outline-info btn-block" @click="addElement(elementArray)">
				<span class="icon-new"></span>
				{{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
			</button>
			<!-- Modals -->
			<add-element-modal id="add-element" :allowedChildren="allowedChildren" @selection="insertElem"></add-element-modal>
		</v-content>
	</div>
</template>

<script>
  import draggable from 'vuedraggable';
  import {notifications} from "./../app/Notifications";

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
        sizeArray: [],
        grid_selected: '',
        column_selected: '',
        elementArray: this.grid,
        showSettings: false,
        selectedSettings: '',
        parent: '',
        allowedChildren: [],
        elements: window.Joomla.getOptions('com_templates').elements
      };
    },
    watch: {
      grid: {
        handler: function (newVal) {
          document.getElementById('jform_params_grid').value = JSON.stringify(newVal);
        },
        deep: true,
      },
    },
    components: {
        draggable
    },
    methods: {
      addGrid(sizes) {
        this.sizeArray = [];
        sizes.forEach(size => {
          this.sizeArray.push({
            type: 'Column',
            options: {
              size: 'col-' + size,
              class: ''
            },
            children: []
          });
        });
        if(this.parent.children) {
          this.parent.children.push({
            type: 'Grid',
            options: {
              class: '',
            },
            children: this.sizeArray
          });
        }
        else {
          this.parent.push({
            type: 'Grid',
            options: {
              class: '',
            },
            children: this.sizeArray
          });
        }
        this.reset();
        this.hide('add-grid');
      },
      deleteGrid(grid) {
        const index = this.elementArray.indexOf(grid);
        if (index > -1)
          this.elementArray.splice(index, 1);
      },
      addColumn(grid) {
        this.reset();
        this.showSettings = true;
        this.grid_selected = grid;
        this.selectedSettings = 'add-column';
      },
      deleteColumn(grid, column) {
        const index = grid.children.indexOf(column);
        if (index > -1) {
          grid.children.splice(index, 1);
        }
      },
      editPosition(grid, column) {
        this.reset();
        this.showSettings = true;
        this.grid_selected = grid;
        this.column_selected = column;
        this.selectedSettings = 'edit-position';
      },
      editColumn(column) {
        this.reset();
        this.showSettings = true;
        this.column_selected = column;
        this.selectedSettings = 'edit-column';
      },
      editGrid(grid) {
        this.reset();
        this.showSettings = true;
        this.selectedSettings = 'edit-grid';
        this.grid_selected = grid;
      },
      reset() {
        this.showSettings = false;
        this.grid_selected = '';
        this.column_selected = '';
        this.parent = '';
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
      addContainer() {
        if(this.parent.children) {
          this.parent.children.push({
            type: 'Container',
            options: {
              class: ''
            },
            children: []
          })
        }
        else {
          this.parent.push({
            type: 'Container',
            options: {
              class: ''
            },
            children: []
          })
        }
      },
      addElement(parent) {
        this.fillAllowedChildren(parent.type);
        if(parent.type == undefined) {
          this.parent = parent;
          this.show('add-element');
          return;
        }
        this.elements.forEach(el => {
          if((el.name == parent.type) && el.children) {
            this.parent = parent;
            this.show('add-element');
            return;
          }
        })
        notifications.error('COM_TEMPLATES_NO_CHILD_ALLOWED');
      },
      insertElem(element,sizes) {
        if(element == 'Grid') {
          this.addGrid(sizes);
        }
        else if(element == 'Container') {
          this.addContainer();
        }
        else {
          if(this.parent.children) {
            this.parent.children.push({
              type: element,
              options: {
                class: ''
              },
              children: []
            })
          }
          else {
            this.parent.push({
              type: element,
              options: {
                class: ''
              },
              children: []
            })
          }
        }
        this.hide('add-element');
      },
      fillAllowedChildren(name) {
        this.allowedChildren = [];

        // Check if parent is root
        if(name == undefined)
          name = 'root';
        this.elements.forEach(el => {
          el.parent.forEach(item => {
            if(item == name)
              this.allowedChildren.push(el.name);
          })
        })
      }
    }
  };
</script>
