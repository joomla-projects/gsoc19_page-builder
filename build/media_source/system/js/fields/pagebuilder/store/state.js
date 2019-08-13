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
    xl: '100%',
    lg: '992px',
    md: '768px',
    sm: '576px',
    xs: '450px', // Smaller as 'sm'
  }
}
