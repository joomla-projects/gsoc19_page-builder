<template>
<div class="container-fluid row">
  <div id="Settings" class="col-sm-2">
      <h2>Settings</h2>
      <hr>

      <div v-if="edit_position">
        <strong>Edit module position - {{ selected_pos }}</strong>
        <br>
        <label for="module_chrome">Select module chrome</label>
        <br>
        <select id="module_chrome" name="module_chrome" v-model="module_chrome">
          <option value="none">none</option>
          <option value="rounded">rounded</option>
          <option value="table">table</option>
          <option value="horz">horz</option>
          <option value="xhtml">xhtml</option>
          <option value="html5">html5</option>
          <option value="outline">outline</option>
        </select>
        <br>

        <label for="size_input">Select module size</label>
        <input id="size_input" name="size_input" type="text" v-model="size_input">

        <button class="btn btn-primary" @click="save">Save</button>
        <button class="btn btn-secondary" @click="removeActive">Back</button>
      </div>

      <div v-else-if="edit_column">
        <button class="btn btn-primary" @click="">Save</button>
        <button class="btn btn-secondary" @click="">Back</button>
      </div>

      <div v-else-if="edit_grid">
        <strong>Edit Grid</strong>
        <br>
        <label for="column_size">Add new column</label>
        <input id="column_size" name="column_size" type="text" v-model="column_size">
        <br>
        <button class="btn btn-primary" @click="editGrid('',true)">Save</button>
        <button class="btn btn-secondary" @click="">Back</button>
      </div>

      <div v-else>
        <strong>Add new grid</strong>
        <br>
        <label for="grid_type">Select grid system(should add to 12)</label>
        <input id="grid_type" name="grid_type" type="text" v-model="grid_system">
        <br>
        <button class="btn btn-primary" @click="addGrid">Add</button>
        <button class="btn btn-primary" @click="removeGrid">Remove</button>
      </div>
  </div>

  <div id="View" class="col-sm-10">
    <h2>View</h2>
    <hr>
    <draggable v-model="gridArray">
      <div v-for="grid in gridArray" v-model="gridArray" class="draggable">
        <draggable v-model="grid.children" class="row grid-row">
          <div class="list-group-item" v-for="column in grid.children" :class="[column.options.size]" @click="">
            {{column.options.size}}
            (<i>{{column.type}}</i>)
          </div>
        </draggable>
        <button class="btn-primary btn editGrid" @click="editGrid(grid,false)">Edit Grid</button>
      </div>
    </draggable>
  </div>
</div>
</template>

<script>
  import draggable from 'vuedraggable'

  export default {
    data() {
      return {
        myArray: [],
        element: '',
        key: 3,
        module_chrome: 'none',
        size_input: '3',
        edit_grid: false,
        edit_column: false,
        edit_position: false,
        selected_pos: '',
        grid_system: '',
        grid_selected: '',
        column_size: '',
        gridArray: 
        [
          {
            type: 'grid',
            options: {},
            children: 
            [
              {
                type: 'column',
                options: {
                  size: 'col-sm-4'
                },
                children: {

                }
              },
              {
                type: 'column',
                options: {
                  size: 'col-sm-4'
                },
                children: {

                }
              },
              {
                type: 'column',
                options: {
                  size: 'col-sm-4'
                },
                children: {

                }
              }
            ]
          }
        ]
      }
    },
    components: {
      draggable
    },
    methods: {
      add() {
        this.key++;
        this.myArray.push({
          name: "pos-" + this.key ,
          module_chrome: this.module_chrome,
          size: "col-sm-" + this.size_input
        }
        );
        this.module_chrome = 'none';
        this.size_input = '3';
      },
      addGrid() {
        var gridSize = this.grid_system.split(" ");
        this.myArray = []
        gridSize.forEach(element => {
          this.myArray.push({
            type: 'column',
            options: {
              size: "col-sm-" + element
            },
            children: {

            }
          })
        });
        this.gridArray.push({
          type: 'grid',
          options: {},
          children: this.myArray
        })
        this.grid_system = '';
      },
      remove() {
        this.myArray.pop();
        this.key--;
        this.module_chrome = 'none';
        this.size_input = '3';
      },
      removeGrid() {
        this.gridArray.pop();
        this.grid_system = '';
      },
      editColumn(element,grid) {
        this.edit_column = true;
        
      },
      editGrid(grid,submit) {
        if(submit){
          this.grid_selected.children.push({
            type: 'column',
            options: {
              size: "col-sm-" + this.column_size
            },
            children: {

            }
          })
          console.log(this.grid_selected);
          this.grid_selected = ''
        }
        else{
          this.edit_grid = true;
          this.grid_selected = grid;
          console.log(grid);
        }
      },
      removeActive() {
        this.selected = false;
        this.module_chrome = 'none';
        this.size_input = '3';
        this.selected_pos = '';
      },
      save() {
        this.myArray[this.selected_pos[4]-1].module_chrome = this.module_chrome;
        this.myArray[this.selected_pos[4]-1].size = "col-sm-" + this.size_input;
      },
      log(el) {
        console.log(el);
      }
    }
  }
</script>

<style>
  button {
    margin: 0.1em
  }
  select,input {
    margin: 0.5em
  }
  h2 {
    margin-top: 0.5em
  }
  .list-group-item {
    margin: 15px 2.5px 15px 2.5px ;
  }
  .list-group-item:last-child {
    margin: 15px 2.5px 15px 2.5px ;
  }
  .list-group-item:hover {
    background-color: lightgray;
    cursor: grab
  }
  .draggable {
    background-color: gray;
    margin-top: 10px;
  }
  .editGrid {
    height: 35px;
  }
  .grid-row {
    margin-left: 15px;
  }
</style>
