/**
 * Translate plugin
 */

const Translate = {};

Translate.translate = key => Joomla.JText._(key, key);

Translate.sprintf = (string, ...args) => {
  const translated = this.translate(string);
  let i = 0;
  return translated.replace(/%((%)|s|d)/g, (m) => {
    let val = args[i];

    if (m === '%d') {
      val = parseFloat(val) || 0;
    }
    i += 1;
    return val;
  });
};

Translate.install = (Vue) => {
  Vue.mixin({
    methods: {
      translate: key => Translate.translate(key),
      sprintf: (key, ...args) => Translate.sprintf(key, args),
    },
  });
};

export default Translate;
