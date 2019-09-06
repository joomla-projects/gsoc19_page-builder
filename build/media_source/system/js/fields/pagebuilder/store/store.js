import Vue from 'vue';
import Vuex from 'vuex';
import state from './state';
import createPersistedState from 'vuex-persistedstate';
import { persistedStateOptions } from './plugins/persistedstate';

Vue.use(Vuex);

const mutations = {
  mapElements(state, elements) {
    state.elementArray = elements;
    state.parent = elements;
    mutations.setMaxKey(state);
  },
  checkAllowedElements(state) {
    state.elements.forEach((el) => {
      if (el.children) {
        state.childAllowed.push(el.id);
      }
      if (el.component) {
        state.componentAllowed.push(el.id);
      }
      if (el.message) {
        state.messageAllowed.push(el.id);
      }
    });
  },
  fillAllowedChildren(state, name) {
    const itemName = name || 'root';
    state.allowedChildren = [];

    state.elements.forEach((el) => {
      el.parent.forEach((item) => {
        if (item === itemName) {
          state.allowedChildren.push(el);
        }
      });
    });
  },
  setParent(state, parent) {
    mutations.fillAllowedChildren(state, parent.type);
    state.parent = parent.children || parent;
  },
  addElement(state, {name, config, childConfig}) {
    const type = state.elements.find(el => el.id === name);
    state.maxKey += 1;

    const newElement = {
      key: state.maxKey,
      type: type.id,
      title: type.title,
      options: {
        size: {
          xs: 0,
          sm: 0,
          md: 0,
          lg: 0,
          xl: 0,
        },
        class: '',
        offset: {
          xs: 0,
          sm: 0,
          md: 0,
          lg: 0,
          xl: 0,
        },
      },
      children: [],
    };

    newElement.options.size[state.activeDevice] = state.size;
    // Merge config with element.options
    Object.assign(newElement.options, config);

    if (name === 'grid' && childConfig) {
      newElement.children = mutations.getConfiguredChildren(state, 'column', childConfig);
    }

    state.parent.push(newElement);
  },

  setAdvancedSettings(state, payload) {
    for (let key in payload) {
      state.advancedSettings[key] = payload[key]
    }

  },
  getConfiguredChildren(state, name, configs) {
    const type = state.elements.find(el => el.id === name);
    const children = [];

    // TODO: config could be anything (not only size) => make key-value object (?)
    if (configs) {
      configs.forEach((config) => {
        state.maxKey += 1;

        let newChild = {
          key: state.maxKey,
          type: type.id,
          title: type.title,
          options: {
            size: {
              xs: 0,
              sm: 0,
              md: 0,
              lg: 0,
              xl: 0,
            },
            class: '',
            offset: {
              xs: 0,
              sm: 0,
              md: 0,
              lg: 0,
              xl: 0,
            },
          },
          children: []
        };
        newChild.options.size[state.activeDevice] = config;

        children.push(newChild);
      });
    }

    return children;
  },
  setMaxKey(state) {
    // Inner function that goes recursive through all items
    const findMaxKey = (elements) => {
      elements.forEach((element) => {
        state.maxKey = Math.max(element.key, state.maxKey);
        findMaxKey(element.children);
      });
    };

    findMaxKey(state.elementArray);
  },
  editElement(state, {element, parent}) {
    state.selectedSettings = 'edit-element';
    state.elementSelected = element;
    state.parent = parent;
    mutations.openNav();
  },
  checkComponent(state, element) {
    if (element.options.component) {
      mutations.restorePosition(state, 'component');
    }
    if (element.options.message) {
      mutations.restorePosition(state, 'message');
    }
    if (element.children) {
      element.children.forEach(child => this.checkComponent(state, child));
    }
  },
  deleteElement(state, {element, parent}) {
    const elements = parent ? parent.children : state.elementArray;
    const index = elements.indexOf(element);
    mutations.checkComponent(state, element);
    if (index > -1) {
      elements.splice(index, 1);
    }
  },
  closeNav() {
    document.getElementById('sidebar').style.width = '0';
    document.getElementById('pagebuilder').style.marginLeft = 'auto';
  },
  openNav() {
    document.getElementById('sidebar').style.width = '250px';
    document.getElementById('pagebuilder').style.marginLeft = '250px';
  },
  modifyElement(state, payload) {
    state.elementSelected.options.class = payload.class;
    if (state.elementSelected.type === 'column') {
      state.elementSelected.options.offset = payload.offset;
    }
  },
  updateElementArray(state, payload) {
    state.elementArray = payload;
  },
  updateDeviceWidth(state, device) {
    state.activeDevice = device;
  },
  updateChildrenOrder(state, {parent, children}) {
    parent.children = children;
  },
  updateGrid(state) {
    document.getElementById('jform_params_grid').value = JSON.stringify(state.elementArray);
  },
  restorePosition(state, location) {
    let element = document.getElementsByClassName('drag_' + location)[0];
    if (element) {
      element.__vue__.$data.element.options[location] = false;
      element.classList.remove('drag_' + location);
    }
    document.getElementById('placeholder_' + location).appendChild(document.getElementById('drag_' + location));
    mutations.updateGrid(state);
  }
};

const getters = {
  getElementSize: state => (element) => {
    if (element.options && element.options.size) {
      const size = element.options.size[state.activeDevice];

      // Get size above (mobile-first principle)
      if (!size) {
        const deviceOrder = Object.keys(state.resolution);
        const activeIndex = deviceOrder.indexOf(state.activeDevice);

        for (let index = activeIndex; index >= 0; index -= 1) {
          const device = deviceOrder[index];
          if (element.options.size[device]) {
            return element.options.size[device];
          }
        }

        return state.size;
      }

      return size;
    }

    return state.size;
  },
  getType: state => (element) => {
    return state.elements.find(el => el.id === element.type || element.id);
  },
  getAdvancedSettings: state => () => {
    return state.advancedSettings
  }
};

export default new Vuex.Store({
  state,
  mutations,
  getters,
  plugins: [createPersistedState(persistedStateOptions)],
  strict: false,
});
