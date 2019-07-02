<template>
	<div>
        <div id="sidebar" class="sidebar">
            <h2>{{ translate('COM_TEMPLATES_SETTINGS') }}</h2>
			<hr>
            <button type="button" class="btn btn-lg closebtn" @click="closeNav()">
                <span class="icon-cancel"></span>
            </button>
            <component :is="selectedSettings" class="form-group" :grid='grid_selected' :column='column_selected' @reset="reset"></component>
        </div>

		<div class="pagebuilder" id="pagebuilder">
			<h2>{{ translate('COM_TEMPLATES_VIEW') }}</h2>
			<!-- Element -->
			<draggable v-model="elementArray" ghost-class="drop">
				<div v-for="element in elementArray" :class="['row-wrapper', element.type]">
                    <span>{{ element.type }}</span>
                    <span v-if="element.options.class != ''">.{{element.options.class}}</span>
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

                        <!-- Container & Module Position -->
                        <div v-if="element.type != 'Grid'">
                            <draggable v-model="element.children">
                                <div v-for="child in element.children" :class="['col-wrapper', child.type]">
                                    <span>{{ child.type }}</span>
                                    <span v-if="child.options.class != ''">.{{ child.options.class }}</span>
                                    
                                    <div v-if="child.type == 'Grid'">
                                        <grid :element="child" :childAllowed="childAllowed" @editColumn="editColumn" @deleteColumn="deleteColumn" @addElement="addElement"></grid>   

                                        <button class="btn btn-add btn-outline-info" type="button" @click="addColumn(child)">
                                        <span class="icon-new"></span>
                                        {{ translate('COM_TEMPLATES_ADD_COLUMN') }}
                                        </button>
                                    </div>

                                    <button v-if="childAllowed.includes(child.type)" class="btn btn-add btn-outline-info" type="button" @click="addElement(child)">
                                        <span class="icon-new"></span>
                                        {{ translate('COM_TEMPLATES_ADD_ELEMENT') }}
                                    </button>
                                </div>
                            </draggable>
                        </div>
                        <!-- Container & Module Position Ends -->

                        <!-- Grid -->
                        <div v-if="element.type == 'Grid'">
                            <grid :element="element" :childAllowed="childAllowed" @editColumn="editColumn" @deleteColumn="deleteColumn" @addElement="addElement"></grid>   

                            <button class="btn btn-add btn-outline-info" type="button" @click="addColumn(element)">
                                <span class="icon-new"></span>
                                {{ translate('COM_TEMPLATES_ADD_COLUMN') }}
                            </button>
                        </div>
                        <!-- Grid Ends-->

                    <button type="button" v-if="childAllowed.includes(element.type)" class="btn btn-add btn-outline-info btn-block" @click="addElement(element)">
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

		</div>
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
        childAllowed: [],
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
    created() {
        this.elements.forEach(el => {
            if(el.children)
                this.childAllowed.push(el.name);
        })
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
      deleteElement(element) {
        const index = this.elementArray.indexOf(element);
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
        document.getElementById("sidebar").style.width = "250px";
        document.getElementById("pagebuilder").style.marginLeft = "250px";
        this.showSettings = true;
        this.column_selected = column;
        this.selectedSettings = 'edit-column';
      },
      editElement(element) {
        this.reset();
        document.getElementById("sidebar").style.width = "250px";
        document.getElementById("pagebuilder").style.marginLeft = "250px";
        this.showSettings = true;
        this.selectedSettings = 'edit-grid';
        this.grid_selected = element;
      },
      reset() {
        this.showSettings = false;
        this.grid_selected = '';
        this.column_selected = '';
        this.parent = '';
        this.closeNav();
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
        this.parent = parent;
        this.show('add-element');
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
              this.allowedChildren.push({
                'name': el.name,
                'description': el.description
              });
          })
        })
      },
      closeNav() {
            document.getElementById("sidebar").style.width = "0";
            document.getElementById("pagebuilder").style.marginLeft = "0";
      }
    }
  }
</script>
