import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const state = {
  elementSelected: '',
  columnSelected: '',
  selectedSettings: '',
  parent: '',
  allowedChildren: [],
  childAllowed: [],
  elements: window.Joomla.getOptions('com_templates').elements,
  elementArray: {},
  gridSize: 12, // TODO: save and load into grid param
};

const mutations = {
  mapGrid(state, elements) {
    state.elementArray = elements;
  },
  ifChildAllowed(state) {
    state.elements.forEach(el => {
      if (el.children) {
        state.childAllowed.push(el.name);
      }
    });
  },
  fillAllowedChildren(state, name) {
    name = name || 'root';
    state.allowedChildren = [];

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
  insertElement(state, element) {
    const newElement = {
      'type': element,
      'options': {
        'class': ''
      },
      'children': []
    };
    if (state.parent.children) {
      state.parent.children.push(newElement);
    } else {
      state.parent.push(newElement);
    }
  },
  addElement(state, parent) {
    state.parent = parent;
  },
  editElement(state, element) {
    state.selectedSettings = 'edit-element';
    state.elementSelected = element;
    mutations.openNav();
  },
  deleteElement(state, element) {
    const index = state.elementArray.indexOf(element);
    if (index > -1) {
      state.elementArray.splice(index, 1);
    }
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
  deleteColumn(state, payload) {
    const index = payload.element.children.indexOf(payload.column);
    if (index > -1) {
      payload.element.children.splice(index, 1);
    }
  },
  closeNav() {
    document.getElementById('sidebar').style.width = '0';
    document.getElementById('pagebuilder').style.marginLeft = '0';
  },
  openNav() {
    document.getElementById('sidebar').style.width = '250px';
    document.getElementById('pagebuilder').style.marginLeft = '250px';
  },
  updateGridBackground(state) {
    const rows = document.querySelectorAll('.pagebuilder .row-wrapper');
    Array.prototype.forEach.call(rows, row => {
      const percentageWidth = (1 / state.gridSize) * 100;
      const pixelWidth = (row.getBoundingClientRect().width / 100) * percentageWidth;
      row.style.backgroundSize = `${pixelWidth}px 150px`;
    });
  },
  modifyElement(state, payload) {
    state.elementSelected.options.class = payload;
  },
};

export default new Vuex.Store({
  state,
  mutations,
});
