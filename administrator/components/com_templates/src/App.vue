<template>
<div class="container-fluid row">
  <div id="Settings" class="col-sm-2">
      <h2>Settings</h2>
      <hr>

      <p v-if="selected"><strong>Edit a module position</strong></p>

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

      <div v-if="!selected">
        <button class="btn btn-primary" @click="add">Add</button>
        <button class="btn btn-primary" @click="remove">Remove</button>
      </div>
      <div v-else>
        <button class="btn btn-primary" @click="">Save</button>
      </div>
  </div>

  <div id="View" class="col-sm-7">
    <h2>View</h2>
    <hr>
    <draggable v-model="myArray">
      <div class='list-group-item'
                  tabindex="-1"
                  v-for="element in myArray"
                  :key="key"
                  :class="element.size"
                  @focus="makeActive(element)"
                  @blur="removeActive">
                  {{element.name}}
      </div>
    </draggable>
  </div>
  
  <div class="col-sm-3">
    <h2>List</h2>
    <hr>
    <rawDisplayer :value="myArray" />
  </div>

</div>
</template>

<script>
  import draggable from 'vuedraggable'
  import rawDisplayer from './components/raw-displayer'

  export default {
    data() {
      return {
        myArray: [
        {
          name: 'pos-1',
          module_chrome: 'none',
          size: 'col-sm-3'
        },
        {
          name: 'pos-2',
          module_chrome: 'none',
          size: 'col-sm-3'
        },
        {
          name: 'pos-3',
          module_chrome: 'none',
          size: 'col-sm-3'
        }],
        element: '',
        key: 3,
        module_chrome: 'none',
        size_input: '3',
        selected: false,
      }
    },
    components: {
      draggable,
      rawDisplayer
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
      remove() {
        this.myArray.pop();
        this.key--;
        this.module_chrome = 'none';
        this.size_input = '3';
      },
      makeActive(element) {
        this.selected = true;
        this.module_chrome = element.module_chrome;
        this.size_input = element.size[7];
      },
      removeActive() {
        this.selected = false;
        this.module_chrome = 'none';
        this.size_input = '3';
      }
    },
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
  .list-group-item:hover {
    background-color: lightgray
  }
  .list-group-item:focus {
    background-color: grey
  }
</style>
