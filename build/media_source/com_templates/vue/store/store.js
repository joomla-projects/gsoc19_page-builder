import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const state = {
  gridSelected: '',
  columnSelected: '',
  selectedSettings: '',
  parent: '',
  allowedChildren: [],
  childAllowed: [],
  elements: window.Joomla.getOptions('com_templates').elements,
  elementArray: {}
};

const actions = {};

const mutations = {
  mapGrid(state, payload) {
    state.elementArray = payload;
  },
  ifChildAllowed(state) {
    state.elements.forEach(el => {
      if (el.children) {
        state.childAllowed.push(el.name);
      }
    });
  },
  addGrid(state, sizes) {
    const newElements = [];

    sizes.forEach(size => {
      newElements.push({
        type: 'Column',
        options: {
          size: size,
          class: ''
        },
        children: []
      });
    });

    const newGrid = {
      type: 'Grid',
      options: {
        class: '',
      },
      children: newElements
    };

    if (state.parent.children) {
      state.parent.children.push(newGrid);
    } else {
      state.parent.push(newGrid);
    }
  },
  deleteElement(state, element) {
    const index = state.elementArray.indexOf(element);
    if (index > -1) {
      state.elementArray.splice(index, 1);
    }
  },
  addColumn(state, grid) {
    state.gridSelected = grid;
    state.selectedSettings = 'add-column';
  },
  editElement(state, element) {
    mutations.openNav();
    state.selectedSettings = 'edit-grid';
    state.gridSelected = element;
  },
  closeNav() {
    document.getElementById('sidebar').style.width = '0';
    document.getElementById('pagebuilder').style.marginLeft = '0';
  },
  openNav() {
    document.getElementById('sidebar').style.width = '250px';
    document.getElementById('pagebuilder').style.marginLeft = '250px';
  },
  addContainer(state) {
    const newContainer = {
      type: 'Container',
      options: {
        class: ''
      },
      children: []
    };
    if (state.parent.children) {
      state.parent.children.push(newContainer);
    } else {
      state.parent.push(newContainer);
    }
  },
  fillAllowedChildren(state, name) {
    state.allowedChildren = [];

    // Check if parent is root
    if (!name) {
      name = 'root';
    }

    state.elements.forEach(el => {
      el.parent.forEach(item => {
        if (item === name) {
          state.allowedChildren.push({
            'name': el.name,
            'description': el.description
          });
        }
      });
    });
  },
  addElement(state, parent) {
    state.parent = parent;
  },
  deleteColumn(state, payload) {
    const index = payload.element.children.indexOf(payload.column);
    if (index > -1) {
      payload.element.children.splice(index, 1);
    }
  },
  insertElem(state, element) {
    const newElement = {
      type: element,
      options: {
        class: ''
      },
      children: []
    };
    if (state.parent.children) {
      state.parent.children.push(newElement);
    } else {
      state.parent.push(newElement);
    }
  }
};

const getters = {};

export default new Vuex.Store({
  state,
  getters,
  actions,
  mutations,
});
