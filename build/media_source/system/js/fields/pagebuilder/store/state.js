/**
 * Vuex pagebuilder state with default values
 */

// Get data from joomla option storage
const joptions = Joomla.getOptions('system.pagebuilder');
if (!joptions) {
  throw new Error('Missing pagebuilder options.');
}

// The initial state
export default {
  elementSelected: '',
  columnSelected: '',
  selectedSettings: '',
  parent: '',
  allowedChildren: [],
  childAllowed: [],
  componentAllowed: [],
  messageAllowed: [],
  elements: joptions.elements,
  fieldID: joptions.id,
  elementArray: {},
  maxKey: 0,
  size: 12,
  activeDevice: 'sm',
  resolution: {
    xs: '450px', // Smaller as 'sm',
    sm: '576px',
    md: '768px',
    lg: '992px',
    xl: '1200px'
  },
  advancedSettings: {
    bgColor: '',
    baseColor: '',
    linkColor: ''
  }
}
