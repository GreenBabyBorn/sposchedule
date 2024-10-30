import {
  focus,
  isAndroid,
  getFirstFocusableElement,
  getLastFocusableElement,
  addStyle,
  relativePosition,
  getOuterWidth,
  absolutePosition,
  isTouchDevice,
  isVisible,
  getFocusableElements,
  findSingle,
} from '@primeuix/utils/dom';
import {
  resolveFieldData,
  isPrintableCharacter,
  isNotEmpty,
  equals,
  findLastIndex,
  isEmpty,
} from '@primeuix/utils/object';
import { ZIndex } from '@primeuix/utils/zindex';
import { FilterService } from '@primevue/core/api';
import {
  UniqueComponentId,
  ConnectedOverlayScrollHandler,
} from '@primevue/core/utils';
import BlankIcon from '@primevue/icons/blank';
import CheckIcon from '@primevue/icons/check';
import ChevronDownIcon from '@primevue/icons/chevrondown';
import SearchIcon from '@primevue/icons/search';
import SpinnerIcon from '@primevue/icons/spinner';
import TimesIcon from '@primevue/icons/times';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import OverlayEventBus from 'primevue/overlayeventbus';
import Portal from 'primevue/portal';
import Ripple from 'primevue/ripple';
import VirtualScroller from 'primevue/virtualscroller';
import BaseComponent from '@primevue/core/basecomponent';
import SelectStyle from 'primevue/select/style';
import {
  resolveComponent,
  resolveDirective,
  openBlock,
  createElementBlock,
  mergeProps,
  renderSlot,
  createTextVNode,
  toDisplayString,
  normalizeClass,
  createBlock,
  resolveDynamicComponent,
  createCommentVNode,
  createElementVNode,
  createVNode,
  withCtx,
  Transition,
  normalizeProps,
  createSlots,
  Fragment,
  renderList,
  withDirectives,
} from 'vue';

var script$1 = {
  name: 'BaseSelect',
  extends: BaseComponent,
  props: {
    modelValue: null,
    options: Array,
    optionLabel: [String, Function],
    optionValue: [String, Function],
    optionDisabled: [String, Function],
    optionGroupLabel: [String, Function],
    optionGroupChildren: [String, Function],
    scrollHeight: {
      type: String,
      default: '14rem',
    },
    filter: Boolean,
    filterPlaceholder: String,
    filterLocale: String,
    filterMatchMode: {
      type: String,
      default: 'contains',
    },
    filterFields: {
      type: Array,
      default: null,
    },
    editable: Boolean,
    placeholder: {
      type: String,
      default: null,
    },
    variant: {
      type: String,
      default: null,
    },
    invalid: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    dataKey: null,
    showClear: {
      type: Boolean,
      default: false,
    },
    fluid: {
      type: Boolean,
      default: null,
    },
    inputId: {
      type: String,
      default: null,
    },
    inputClass: {
      type: [String, Object],
      default: null,
    },
    inputStyle: {
      type: Object,
      default: null,
    },
    labelId: {
      type: String,
      default: null,
    },
    labelClass: {
      type: [String, Object],
      default: null,
    },
    labelStyle: {
      type: Object,
      default: null,
    },
    panelClass: {
      type: [String, Object],
      default: null,
    },
    overlayStyle: {
      type: Object,
      default: null,
    },
    overlayClass: {
      type: [String, Object],
      default: null,
    },
    panelStyle: {
      type: Object,
      default: null,
    },
    appendTo: {
      type: [String, Object],
      default: 'body',
    },
    loading: {
      type: Boolean,
      default: false,
    },
    clearIcon: {
      type: String,
      default: undefined,
    },
    dropdownIcon: {
      type: String,
      default: undefined,
    },
    filterIcon: {
      type: String,
      default: undefined,
    },
    loadingIcon: {
      type: String,
      default: undefined,
    },
    resetFilterOnHide: {
      type: Boolean,
      default: false,
    },
    resetFilterOnClear: {
      type: Boolean,
      default: false,
    },
    virtualScrollerOptions: {
      type: Object,
      default: null,
    },
    autoOptionFocus: {
      type: Boolean,
      default: false,
    },
    autoFilterFocus: {
      type: Boolean,
      default: false,
    },
    selectOnFocus: {
      type: Boolean,
      default: false,
    },
    focusOnHover: {
      type: Boolean,
      default: true,
    },
    highlightOnSelect: {
      type: Boolean,
      default: true,
    },
    checkmark: {
      type: Boolean,
      default: false,
    },
    filterMessage: {
      type: String,
      default: null,
    },
    selectionMessage: {
      type: String,
      default: null,
    },
    emptySelectionMessage: {
      type: String,
      default: null,
    },
    emptyFilterMessage: {
      type: String,
      default: null,
    },
    emptyMessage: {
      type: String,
      default: null,
    },
    tabindex: {
      type: Number,
      default: 0,
    },
    ariaLabel: {
      type: String,
      default: null,
    },
    ariaLabelledby: {
      type: String,
      default: null,
    },
  },
  style: SelectStyle,
  provide: function provide() {
    return {
      $pcSelect: this,
      $parentInstance: this,
    };
  },
};

function _typeof(o) {
  '@babel/helpers - typeof';
  return (
    (_typeof =
      'function' == typeof Symbol && 'symbol' == typeof Symbol.iterator
        ? function (o) {
            return typeof o;
          }
        : function (o) {
            return o &&
              'function' == typeof Symbol &&
              o.constructor === Symbol &&
              o !== Symbol.prototype
              ? 'symbol'
              : typeof o;
          }),
    _typeof(o)
  );
}
function _toConsumableArray(r) {
  return (
    _arrayWithoutHoles(r) ||
    _iterableToArray(r) ||
    _unsupportedIterableToArray(r) ||
    _nonIterableSpread()
  );
}
function _nonIterableSpread() {
  throw new TypeError(
    'Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.'
  );
}
function _unsupportedIterableToArray(r, a) {
  if (r) {
    if ('string' == typeof r) return _arrayLikeToArray(r, a);
    var t = {}.toString.call(r).slice(8, -1);
    return (
      'Object' === t && r.constructor && (t = r.constructor.name),
      'Map' === t || 'Set' === t
        ? Array.from(r)
        : 'Arguments' === t ||
            /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t)
          ? _arrayLikeToArray(r, a)
          : void 0
    );
  }
}
function _iterableToArray(r) {
  if (
    ('undefined' != typeof Symbol && null != r[Symbol.iterator]) ||
    null != r['@@iterator']
  )
    return Array.from(r);
}
function _arrayWithoutHoles(r) {
  if (Array.isArray(r)) return _arrayLikeToArray(r);
}
function _arrayLikeToArray(r, a) {
  (null == a || a > r.length) && (a = r.length);
  for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
  return n;
}
function ownKeys(e, r) {
  var t = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var o = Object.getOwnPropertySymbols(e);
    r &&
      (o = o.filter(function (r) {
        return Object.getOwnPropertyDescriptor(e, r).enumerable;
      })),
      t.push.apply(t, o);
  }
  return t;
}
function _objectSpread(e) {
  for (var r = 1; r < arguments.length; r++) {
    var t = null != arguments[r] ? arguments[r] : {};
    r % 2
      ? ownKeys(Object(t), !0).forEach(function (r) {
          _defineProperty(e, r, t[r]);
        })
      : Object.getOwnPropertyDescriptors
        ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t))
        : ownKeys(Object(t)).forEach(function (r) {
            Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
          });
  }
  return e;
}
function _defineProperty(e, r, t) {
  return (
    (r = _toPropertyKey(r)) in e
      ? Object.defineProperty(e, r, {
          value: t,
          enumerable: !0,
          configurable: !0,
          writable: !0,
        })
      : (e[r] = t),
    e
  );
}
function _toPropertyKey(t) {
  var i = _toPrimitive(t, 'string');
  return 'symbol' == _typeof(i) ? i : i + '';
}
function _toPrimitive(t, r) {
  if ('object' != _typeof(t) || !t) return t;
  var e = t[Symbol.toPrimitive];
  if (void 0 !== e) {
    var i = e.call(t, r || 'default');
    if ('object' != _typeof(i)) return i;
    throw new TypeError('@@toPrimitive must return a primitive value.');
  }
  return ('string' === r ? String : Number)(t);
}
var script = {
  name: 'Select',
  extends: script$1,
  inheritAttrs: false,
  emits: [
    'update:modelValue',
    'change',
    'focus',
    'blur',
    'before-show',
    'before-hide',
    'show',
    'hide',
    'filter',
  ],
  inject: {
    $pcFluid: {
      default: null,
    },
  },
  outsideClickListener: null,
  scrollHandler: null,
  resizeListener: null,
  labelClickListener: null,
  overlay: null,
  list: null,
  virtualScroller: null,
  searchTimeout: null,
  searchValue: null,
  isModelValueChanged: false,
  data: function data() {
    return {
      id: this.$attrs.id,
      clicked: false,
      focused: false,
      focusedOptionIndex: -1,
      filterValue: null,
      overlayVisible: false,
    };
  },
  watch: {
    '$attrs.id': function $attrsId(newValue) {
      this.id = newValue || UniqueComponentId();
    },
    modelValue: function modelValue() {
      this.isModelValueChanged = true;
    },
    options: function options() {
      this.autoUpdateModel();
    },
  },
  mounted: function mounted() {
    this.id = this.id || UniqueComponentId();
    this.autoUpdateModel();
    this.bindLabelClickListener();
  },
  updated: function updated() {
    if (this.overlayVisible && this.isModelValueChanged) {
      this.scrollInView(this.findSelectedOptionIndex());
    }
    this.isModelValueChanged = false;
  },
  beforeUnmount: function beforeUnmount() {
    this.unbindOutsideClickListener();
    this.unbindResizeListener();
    this.unbindLabelClickListener();
    if (this.scrollHandler) {
      this.scrollHandler.destroy();
      this.scrollHandler = null;
    }
    if (this.overlay) {
      ZIndex.clear(this.overlay);
      this.overlay = null;
    }
  },
  methods: {
    getOptionIndex: function getOptionIndex(index, fn) {
      return this.virtualScrollerDisabled ? index : fn && fn(index)['index'];
    },
    getOptionLabel: function getOptionLabel(option) {
      return this.optionLabel
        ? resolveFieldData(option, this.optionLabel)
        : option;
    },
    getOptionValue: function getOptionValue(option) {
      return this.optionValue
        ? resolveFieldData(option, this.optionValue)
        : option;
    },
    getOptionRenderKey: function getOptionRenderKey(option, index) {
      return (
        (this.dataKey
          ? resolveFieldData(option, this.dataKey)
          : this.getOptionLabel(option)) +
        '_' +
        index
      );
    },
    getPTItemOptions: function getPTItemOptions(
      option,
      itemOptions,
      index,
      key
    ) {
      return this.ptm(key, {
        context: {
          option: option,
          index: index,
          selected: this.isSelected(option),
          focused:
            this.focusedOptionIndex === this.getOptionIndex(index, itemOptions),
          disabled: this.isOptionDisabled(option),
        },
      });
    },
    isOptionDisabled: function isOptionDisabled(option) {
      return this.optionDisabled
        ? resolveFieldData(option, this.optionDisabled)
        : false;
    },
    isOptionGroup: function isOptionGroup(option) {
      return this.optionGroupLabel && option.optionGroup && option.group;
    },
    getOptionGroupLabel: function getOptionGroupLabel(optionGroup) {
      return resolveFieldData(optionGroup, this.optionGroupLabel);
    },
    getOptionGroupChildren: function getOptionGroupChildren(optionGroup) {
      return resolveFieldData(optionGroup, this.optionGroupChildren);
    },
    getAriaPosInset: function getAriaPosInset(index) {
      var _this = this;
      return (
        (this.optionGroupLabel
          ? index -
            this.visibleOptions.slice(0, index).filter(function (option) {
              return _this.isOptionGroup(option);
            }).length
          : index) + 1
      );
    },
    show: function show(isFocus) {
      this.$emit('before-show');
      this.overlayVisible = true;
      this.focusedOptionIndex =
        this.focusedOptionIndex !== -1
          ? this.focusedOptionIndex
          : this.autoOptionFocus
            ? this.findFirstFocusedOptionIndex()
            : this.editable
              ? -1
              : this.findSelectedOptionIndex();
      isFocus && focus(this.$refs.focusInput);
    },
    hide: function hide(isFocus) {
      var _this2 = this;
      var _hide = function _hide() {
        _this2.$emit('before-hide');
        _this2.overlayVisible = false;
        _this2.clicked = false;
        _this2.focusedOptionIndex = -1;
        _this2.searchValue = '';
        _this2.resetFilterOnHide && (_this2.filterValue = null);
        isFocus && focus(_this2.$refs.focusInput);
      };
      setTimeout(function () {
        _hide();
      }, 0); // For ScreenReaders
    },
    onFocus: function onFocus(event) {
      if (this.disabled) {
        // For ScreenReaders
        return;
      }
      this.focused = true;
      if (this.overlayVisible) {
        this.focusedOptionIndex =
          this.focusedOptionIndex !== -1
            ? this.focusedOptionIndex
            : this.autoOptionFocus
              ? this.findFirstFocusedOptionIndex()
              : this.editable
                ? -1
                : this.findSelectedOptionIndex();
        this.scrollInView(this.focusedOptionIndex);
      }
      this.$emit('focus', event);
    },
    onBlur: function onBlur(event) {
      this.focused = false;
      this.focusedOptionIndex = -1;
      this.searchValue = '';
      this.$emit('blur', event);
    },
    onKeyDown: function onKeyDown(event) {
      if (this.disabled || isAndroid()) {
        event.preventDefault();
        return;
      }
      var metaKey = event.metaKey || event.ctrlKey;
      switch (event.code) {
        case 'ArrowDown':
          this.onArrowDownKey(event);
          break;
        case 'ArrowUp':
          this.onArrowUpKey(event, this.editable);
          break;
        case 'ArrowLeft':
        case 'ArrowRight':
          this.onArrowLeftKey(event, this.editable);
          break;
        case 'Home':
          this.onHomeKey(event, this.editable);
          break;
        case 'End':
          this.onEndKey(event, this.editable);
          break;
        case 'PageDown':
          this.onPageDownKey(event);
          break;
        case 'PageUp':
          this.onPageUpKey(event);
          break;
        case 'Space':
          this.onSpaceKey(event, this.editable);
          break;
        case 'Enter':
        case 'NumpadEnter':
          this.onEnterKey(event);
          break;
        case 'Escape':
          this.onEscapeKey(event);
          break;
        case 'Tab':
          this.onTabKey(event);
          break;
        case 'Backspace':
          this.onBackspaceKey(event, this.editable);
          break;
        case 'ShiftLeft':
        case 'ShiftRight':
          //NOOP
          break;
        default:
          if (!metaKey && isPrintableCharacter(event.key)) {
            !this.overlayVisible && this.show();
            !this.editable && this.searchOptions(event, event.key);
          }
          break;
      }
      this.clicked = false;
    },
    onEditableInput: function onEditableInput(event) {
      var value = event.target.value;
      this.searchValue = '';

      var matched = this.searchOptions(event, value);
      !matched && (this.focusedOptionIndex = -1);
      this.updateModel(event, value);
      !this.overlayVisible && isNotEmpty(value) && this.show();
    },
    onContainerClick: function onContainerClick(event) {
      if (this.disabled || this.loading) {
        return;
      }
      if (
        event.target.tagName === 'INPUT' ||
        event.target.getAttribute('data-pc-section') === 'clearicon' ||
        event.target.closest('[data-pc-section="clearicon"]')
      ) {
        return;
      } else if (!this.overlay || !this.overlay.contains(event.target)) {
        this.overlayVisible ? this.hide(true) : this.show(true);
      }
      this.clicked = true;
    },
    onClearClick: function onClearClick(event) {
      this.updateModel(event, null);
      this.resetFilterOnClear && (this.filterValue = null);
    },
    onFirstHiddenFocus: function onFirstHiddenFocus(event) {
      var focusableEl =
        event.relatedTarget === this.$refs.focusInput
          ? getFirstFocusableElement(
              this.overlay,
              ':not([data-p-hidden-focusable="true"])'
            )
          : this.$refs.focusInput;
      focus(focusableEl);
    },
    onLastHiddenFocus: function onLastHiddenFocus(event) {
      var focusableEl =
        event.relatedTarget === this.$refs.focusInput
          ? getLastFocusableElement(
              this.overlay,
              ':not([data-p-hidden-focusable="true"])'
            )
          : this.$refs.focusInput;
      focus(focusableEl);
    },
    onOptionSelect: function onOptionSelect(event, option) {
      var isHide =
        arguments.length > 2 && arguments[2] !== undefined
          ? arguments[2]
          : true;
      var value = this.getOptionValue(option);
      this.updateModel(event, value);
      isHide && this.hide(true);
    },
    onOptionMouseMove: function onOptionMouseMove(event, index) {
      if (this.focusOnHover) {
        this.changeFocusedOptionIndex(event, index);
      }
    },
    onFilterChange: function onFilterChange(event) {
      var value = event.target.value;
      this.filterValue = value;
      this.focusedOptionIndex = -1;
      this.$emit('filter', {
        originalEvent: event,
        value: value,
      });
      !this.virtualScrollerDisabled && this.virtualScroller.scrollToIndex(0);
    },
    onFilterKeyDown: function onFilterKeyDown(event) {
      // Check if the event is part of a text composition process (e.g., for Asian languages).
      // If event.isComposing is true, it means the user is still composing text and the input is not finalized.
      if (event.isComposing) return;
      switch (event.code) {
        case 'ArrowDown':
          this.onArrowDownKey(event);
          break;
        case 'ArrowUp':
          this.onArrowUpKey(event, true);
          break;
        case 'ArrowLeft':
        case 'ArrowRight':
          this.onArrowLeftKey(event, true);
          break;
        case 'Home':
          this.onHomeKey(event, true);
          break;
        case 'End':
          this.onEndKey(event, true);
          break;
        case 'Enter':
        case 'NumpadEnter':
          this.onEnterKey(event);
          break;
        case 'Escape':
          this.onEscapeKey(event);
          break;
        case 'Tab':
          this.onTabKey(event, true);
          break;
      }
    },
    onFilterBlur: function onFilterBlur() {
      this.focusedOptionIndex = -1;
    },
    onFilterUpdated: function onFilterUpdated() {
      if (this.overlayVisible) {
        this.alignOverlay();
      }
    },
    onOverlayClick: function onOverlayClick(event) {
      OverlayEventBus.emit('overlay-click', {
        originalEvent: event,
        target: this.$el,
      });
    },
    onOverlayKeyDown: function onOverlayKeyDown(event) {
      switch (event.code) {
        case 'Escape':
          this.onEscapeKey(event);
          break;
      }
    },
    onArrowDownKey: function onArrowDownKey(event) {
      if (!this.overlayVisible) {
        this.show();
        this.editable &&
          this.changeFocusedOptionIndex(event, this.findSelectedOptionIndex());
      } else {
        var optionIndex =
          this.focusedOptionIndex !== -1
            ? this.findNextOptionIndex(this.focusedOptionIndex)
            : this.clicked
              ? this.findFirstOptionIndex()
              : this.findFirstFocusedOptionIndex();
        this.changeFocusedOptionIndex(event, optionIndex);
      }
      event.preventDefault();
    },
    onArrowUpKey: function onArrowUpKey(event) {
      var pressedInInputText =
        arguments.length > 1 && arguments[1] !== undefined
          ? arguments[1]
          : false;
      if (event.altKey && !pressedInInputText) {
        if (this.focusedOptionIndex !== -1) {
          this.onOptionSelect(
            event,
            this.visibleOptions[this.focusedOptionIndex]
          );
        }
        this.overlayVisible && this.hide();
        event.preventDefault();
      } else {
        var optionIndex =
          this.focusedOptionIndex !== -1
            ? this.findPrevOptionIndex(this.focusedOptionIndex)
            : this.clicked
              ? this.findLastOptionIndex()
              : this.findLastFocusedOptionIndex();
        this.changeFocusedOptionIndex(event, optionIndex);
        !this.overlayVisible && this.show();
        event.preventDefault();
      }
    },
    onArrowLeftKey: function onArrowLeftKey(event) {
      var pressedInInputText =
        arguments.length > 1 && arguments[1] !== undefined
          ? arguments[1]
          : false;
      pressedInInputText && (this.focusedOptionIndex = -1);
    },
    onHomeKey: function onHomeKey(event) {
      var pressedInInputText =
        arguments.length > 1 && arguments[1] !== undefined
          ? arguments[1]
          : false;
      if (pressedInInputText) {
        var target = event.currentTarget;
        if (event.shiftKey) {
          target.setSelectionRange(0, event.target.selectionStart);
        } else {
          target.setSelectionRange(0, 0);
          this.focusedOptionIndex = -1;
        }
      } else {
        this.changeFocusedOptionIndex(event, this.findFirstOptionIndex());
        !this.overlayVisible && this.show();
      }
      event.preventDefault();
    },
    onEndKey: function onEndKey(event) {
      var pressedInInputText =
        arguments.length > 1 && arguments[1] !== undefined
          ? arguments[1]
          : false;
      if (pressedInInputText) {
        var target = event.currentTarget;
        if (event.shiftKey) {
          target.setSelectionRange(
            event.target.selectionStart,
            target.value.length
          );
        } else {
          var len = target.value.length;
          target.setSelectionRange(len, len);
          this.focusedOptionIndex = -1;
        }
      } else {
        this.changeFocusedOptionIndex(event, this.findLastOptionIndex());
        !this.overlayVisible && this.show();
      }
      event.preventDefault();
    },
    onPageUpKey: function onPageUpKey(event) {
      this.scrollInView(0);
      event.preventDefault();
    },
    onPageDownKey: function onPageDownKey(event) {
      this.scrollInView(this.visibleOptions.length - 1);
      event.preventDefault();
    },
    onEnterKey: function onEnterKey(event) {
      if (!this.overlayVisible) {
        this.focusedOptionIndex = -1; // reset
        this.onArrowDownKey(event);
      } else {
        if (this.focusedOptionIndex !== -1) {
          this.onOptionSelect(
            event,
            this.visibleOptions[this.focusedOptionIndex]
          );
        }
        this.hide();
      }
      event.preventDefault();
    },
    onSpaceKey: function onSpaceKey(event) {
      var pressedInInputText =
        arguments.length > 1 && arguments[1] !== undefined
          ? arguments[1]
          : false;
      !pressedInInputText && this.onEnterKey(event);
    },
    onEscapeKey: function onEscapeKey(event) {
      this.overlayVisible && this.hide(true);
      event.preventDefault();
      event.stopPropagation(); //@todo will be changed next versionss
    },
    onTabKey: function onTabKey(event) {
      var pressedInInputText =
        arguments.length > 1 && arguments[1] !== undefined
          ? arguments[1]
          : false;
      if (!pressedInInputText) {
        if (this.overlayVisible && this.hasFocusableElements()) {
          focus(this.$refs.firstHiddenFocusableElementOnOverlay);
          event.preventDefault();
        } else {
          if (this.focusedOptionIndex !== -1) {
            this.onOptionSelect(
              event,
              this.visibleOptions[this.focusedOptionIndex]
            );
          }
          this.overlayVisible && this.hide(this.filter);
        }
      }
    },
    onBackspaceKey: function onBackspaceKey(event) {
      var pressedInInputText =
        arguments.length > 1 && arguments[1] !== undefined
          ? arguments[1]
          : false;
      if (pressedInInputText) {
        !this.overlayVisible && this.show();
      }
    },
    onOverlayEnter: function onOverlayEnter(el) {
      var _this3 = this;
      ZIndex.set('overlay', el, this.$primevue.config.zIndex.overlay);
      addStyle(el, {
        position: 'absolute',
        top: '0',
        left: '0',
      });
      this.alignOverlay();
      this.scrollInView();
      setTimeout(function () {
        _this3.autoFilterFocus && focus(_this3.$refs.filterInput.$el);
      }, 1);
    },
    onOverlayAfterEnter: function onOverlayAfterEnter() {
      this.bindOutsideClickListener();
      this.bindScrollListener();
      this.bindResizeListener();
      this.$emit('show');
    },
    onOverlayLeave: function onOverlayLeave() {
      var _this4 = this;
      this.unbindOutsideClickListener();
      this.unbindScrollListener();
      this.unbindResizeListener();
      if (this.autoFilterFocus && this.filter) {
        this.$nextTick(function () {
          focus(_this4.$refs.filterInput.$el);
        });
      }
      this.$emit('hide');
      this.overlay = null;
    },
    onOverlayAfterLeave: function onOverlayAfterLeave(el) {
      ZIndex.clear(el);
    },
    alignOverlay: function alignOverlay() {
      if (this.appendTo === 'self') {
        relativePosition(this.overlay, this.$el);
      } else {
        this.overlay.style.minWidth = getOuterWidth(this.$el) + 'px';
        absolutePosition(this.overlay, this.$el);
      }
    },
    bindOutsideClickListener: function bindOutsideClickListener() {
      var _this5 = this;
      if (!this.outsideClickListener) {
        this.outsideClickListener = function (event) {
          if (
            _this5.overlayVisible &&
            _this5.overlay &&
            !_this5.$el.contains(event.target) &&
            !_this5.overlay.contains(event.target)
          ) {
            _this5.hide();
          }
        };
        document.addEventListener('click', this.outsideClickListener);
      }
    },
    unbindOutsideClickListener: function unbindOutsideClickListener() {
      if (this.outsideClickListener) {
        document.removeEventListener('click', this.outsideClickListener);
        this.outsideClickListener = null;
      }
    },
    bindScrollListener: function bindScrollListener() {
      var _this6 = this;
      if (!this.scrollHandler) {
        this.scrollHandler = new ConnectedOverlayScrollHandler(
          this.$refs.container,
          function () {
            if (_this6.overlayVisible) {
              _this6.hide();
            }
          }
        );
      }
      this.scrollHandler.bindScrollListener();
    },
    unbindScrollListener: function unbindScrollListener() {
      if (this.scrollHandler) {
        this.scrollHandler.unbindScrollListener();
      }
    },
    bindResizeListener: function bindResizeListener() {
      var _this7 = this;
      if (!this.resizeListener) {
        this.resizeListener = function () {
          if (_this7.overlayVisible && !isTouchDevice()) {
            _this7.hide();
          }
        };
        window.addEventListener('resize', this.resizeListener);
      }
    },
    unbindResizeListener: function unbindResizeListener() {
      if (this.resizeListener) {
        window.removeEventListener('resize', this.resizeListener);
        this.resizeListener = null;
      }
    },
    bindLabelClickListener: function bindLabelClickListener() {
      var _this8 = this;
      if (!this.editable && !this.labelClickListener) {
        var label = document.querySelector(
          'label[for="'.concat(this.labelId, '"]')
        );
        if (label && isVisible(label)) {
          this.labelClickListener = function () {
            focus(_this8.$refs.focusInput);
          };
          label.addEventListener('click', this.labelClickListener);
        }
      }
    },
    unbindLabelClickListener: function unbindLabelClickListener() {
      if (this.labelClickListener) {
        var label = document.querySelector(
          'label[for="'.concat(this.labelId, '"]')
        );
        if (label && isVisible(label)) {
          label.removeEventListener('click', this.labelClickListener);
        }
      }
    },
    hasFocusableElements: function hasFocusableElements() {
      return (
        getFocusableElements(
          this.overlay,
          ':not([data-p-hidden-focusable="true"])'
        ).length > 0
      );
    },
    isOptionMatched: function isOptionMatched(option) {
      var _this$getOptionLabel;
      return (
        this.isValidOption(option) &&
        typeof this.getOptionLabel(option) === 'string' &&
        ((_this$getOptionLabel = this.getOptionLabel(option)) === null ||
        _this$getOptionLabel === void 0
          ? void 0
          : _this$getOptionLabel
              .toLocaleLowerCase(this.filterLocale)
              .startsWith(
                this.searchValue.toLocaleLowerCase(this.filterLocale)
              ))
      );
    },
    isValidOption: function isValidOption(option) {
      return (
        isNotEmpty(option) &&
        !(this.isOptionDisabled(option) || this.isOptionGroup(option))
      );
    },
    isValidSelectedOption: function isValidSelectedOption(option) {
      return this.isValidOption(option) && this.isSelected(option);
    },
    isSelected: function isSelected(option) {
      return equals(
        this.modelValue,
        this.getOptionValue(option),
        this.equalityKey
      );
    },
    findFirstOptionIndex: function findFirstOptionIndex() {
      var _this9 = this;
      return this.visibleOptions.findIndex(function (option) {
        return _this9.isValidOption(option);
      });
    },
    findLastOptionIndex: function findLastOptionIndex() {
      var _this10 = this;
      return findLastIndex(this.visibleOptions, function (option) {
        return _this10.isValidOption(option);
      });
    },
    findNextOptionIndex: function findNextOptionIndex(index) {
      var _this11 = this;
      var matchedOptionIndex =
        index < this.visibleOptions.length - 1
          ? this.visibleOptions.slice(index + 1).findIndex(function (option) {
              return _this11.isValidOption(option);
            })
          : -1;
      return matchedOptionIndex > -1 ? matchedOptionIndex + index + 1 : index;
    },
    findPrevOptionIndex: function findPrevOptionIndex(index) {
      var _this12 = this;
      var matchedOptionIndex =
        index > 0
          ? findLastIndex(
              this.visibleOptions.slice(0, index),
              function (option) {
                return _this12.isValidOption(option);
              }
            )
          : -1;
      return matchedOptionIndex > -1 ? matchedOptionIndex : index;
    },
    findSelectedOptionIndex: function findSelectedOptionIndex() {
      var _this13 = this;
      return this.hasSelectedOption
        ? this.visibleOptions.findIndex(function (option) {
            return _this13.isValidSelectedOption(option);
          })
        : -1;
    },
    findFirstFocusedOptionIndex: function findFirstFocusedOptionIndex() {
      var selectedIndex = this.findSelectedOptionIndex();
      return selectedIndex < 0 ? this.findFirstOptionIndex() : selectedIndex;
    },
    findLastFocusedOptionIndex: function findLastFocusedOptionIndex() {
      var selectedIndex = this.findSelectedOptionIndex();
      return selectedIndex < 0 ? this.findLastOptionIndex() : selectedIndex;
    },
    searchOptions: function searchOptions(event, _char) {
      var _this14 = this;
      this.searchValue = (this.searchValue || '') + _char;
      var optionIndex = -1;
      var matched = false;
      if (isNotEmpty(this.searchValue)) {
        if (this.focusedOptionIndex !== -1) {
          optionIndex = this.visibleOptions
            .slice(this.focusedOptionIndex)
            .findIndex(function (option) {
              return _this14.isOptionMatched(option);
            });
          optionIndex =
            optionIndex === -1
              ? this.visibleOptions
                  .slice(0, this.focusedOptionIndex)
                  .findIndex(function (option) {
                    return _this14.isOptionMatched(option);
                  })
              : optionIndex + this.focusedOptionIndex;
        } else {
          optionIndex = this.visibleOptions.findIndex(function (option) {
            return _this14.isOptionMatched(option);
          });
        }
        if (optionIndex !== -1) {
          matched = true;
        }
        if (optionIndex === -1 && this.focusedOptionIndex === -1) {
          optionIndex = this.findFirstFocusedOptionIndex();
        }
        if (optionIndex !== -1) {
          this.changeFocusedOptionIndex(event, optionIndex);
        }
      }
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout);
      }
      this.searchTimeout = setTimeout(function () {
        _this14.searchValue = '';
        _this14.searchTimeout = null;
      }, 500);
      return matched;
    },
    changeFocusedOptionIndex: function changeFocusedOptionIndex(event, index) {
      if (this.focusedOptionIndex !== index) {
        this.focusedOptionIndex = index;
        this.scrollInView();
        if (this.selectOnFocus) {
          this.onOptionSelect(event, this.visibleOptions[index], false);
        }
      }
    },
    scrollInView: function scrollInView() {
      var _this15 = this;
      var index =
        arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : -1;
      this.$nextTick(function () {
        var id =
          index !== -1
            ? ''.concat(_this15.id, '_').concat(index)
            : _this15.focusedOptionId;
        var element = findSingle(_this15.list, 'li[id="'.concat(id, '"]'));
        if (element) {
          element.scrollIntoView &&
            element.scrollIntoView({
              block: 'nearest',
              inline: 'start',
            });
        } else if (!_this15.virtualScrollerDisabled) {
          _this15.virtualScroller &&
            _this15.virtualScroller.scrollToIndex(
              index !== -1 ? index : _this15.focusedOptionIndex
            );
        }
      });
    },
    autoUpdateModel: function autoUpdateModel() {
      if (
        this.selectOnFocus &&
        this.autoOptionFocus &&
        !this.hasSelectedOption
      ) {
        this.focusedOptionIndex = this.findFirstFocusedOptionIndex();
        this.onOptionSelect(
          null,
          this.visibleOptions[this.focusedOptionIndex],
          false
        );
      }
    },
    updateModel: function updateModel(event, value) {
      this.$emit('update:modelValue', value);
      this.$emit('change', {
        originalEvent: event,
        value: value,
      });
    },
    flatOptions: function flatOptions(options) {
      var _this16 = this;
      return (options || []).reduce(function (result, option, index) {
        result.push({
          optionGroup: option,
          group: true,
          index: index,
        });
        var optionGroupChildren = _this16.getOptionGroupChildren(option);
        optionGroupChildren &&
          optionGroupChildren.forEach(function (o) {
            return result.push(o);
          });
        return result;
      }, []);
    },
    overlayRef: function overlayRef(el) {
      this.overlay = el;
    },
    listRef: function listRef(el, contentRef) {
      this.list = el;
      contentRef && contentRef(el); // For VirtualScroller
    },
    virtualScrollerRef: function virtualScrollerRef(el) {
      this.virtualScroller = el;
    },
  },
  computed: {
    visibleOptions: function visibleOptions() {
      var _this17 = this;
      var options = this.optionGroupLabel
        ? this.flatOptions(this.options)
        : this.options || [];
      if (this.filterValue) {
        var filteredOptions = FilterService.filter(
          options,
          this.searchFields,
          this.filterValue,
          this.filterMatchMode,
          this.filterLocale
        );
        if (this.optionGroupLabel) {
          var optionGroups = this.options || [];
          var filtered = [];
          optionGroups.forEach(function (group) {
            var groupChildren = _this17.getOptionGroupChildren(group);
            var filteredItems = groupChildren.filter(function (item) {
              return filteredOptions.includes(item);
            });
            if (filteredItems.length > 0)
              filtered.push(
                _objectSpread(
                  _objectSpread({}, group),
                  {},
                  _defineProperty(
                    {},
                    typeof _this17.optionGroupChildren === 'string'
                      ? _this17.optionGroupChildren
                      : 'items',
                    _toConsumableArray(filteredItems)
                  )
                )
              );
          });
          return this.flatOptions(filtered);
        }
        return filteredOptions;
      }
      return options;
    },
    hasSelectedOption: function hasSelectedOption() {
      return isNotEmpty(this.modelValue);
    },
    label: function label() {
      var selectedOptionIndex = this.findSelectedOptionIndex();
      return selectedOptionIndex !== -1
        ? this.getOptionLabel(this.visibleOptions[selectedOptionIndex])
        : this.placeholder || 'p-emptylabel';
    },
    editableInputValue: function editableInputValue() {
      var selectedOptionIndex = this.findSelectedOptionIndex();
      return selectedOptionIndex !== -1
        ? this.getOptionLabel(this.visibleOptions[selectedOptionIndex])
        : this.modelValue || '';
    },
    equalityKey: function equalityKey() {
      return this.optionValue ? null : this.dataKey;
    },
    searchFields: function searchFields() {
      return this.filterFields || [this.optionLabel];
    },
    filterResultMessageText: function filterResultMessageText() {
      return isNotEmpty(this.visibleOptions)
        ? this.filterMessageText.replaceAll('{0}', this.visibleOptions.length)
        : this.emptyFilterMessageText;
    },
    filterMessageText: function filterMessageText() {
      return (
        this.filterMessage || this.$primevue.config.locale.searchMessage || ''
      );
    },
    emptyFilterMessageText: function emptyFilterMessageText() {
      return (
        this.emptyFilterMessage ||
        this.$primevue.config.locale.emptySearchMessage ||
        this.$primevue.config.locale.emptyFilterMessage ||
        ''
      );
    },
    emptyMessageText: function emptyMessageText() {
      return (
        this.emptyMessage || this.$primevue.config.locale.emptyMessage || ''
      );
    },
    selectionMessageText: function selectionMessageText() {
      return (
        this.selectionMessage ||
        this.$primevue.config.locale.selectionMessage ||
        ''
      );
    },
    emptySelectionMessageText: function emptySelectionMessageText() {
      return (
        this.emptySelectionMessage ||
        this.$primevue.config.locale.emptySelectionMessage ||
        ''
      );
    },
    selectedMessageText: function selectedMessageText() {
      return this.hasSelectedOption
        ? this.selectionMessageText.replaceAll('{0}', '1')
        : this.emptySelectionMessageText;
    },
    focusedOptionId: function focusedOptionId() {
      return this.focusedOptionIndex !== -1
        ? ''.concat(this.id, '_').concat(this.focusedOptionIndex)
        : null;
    },
    ariaSetSize: function ariaSetSize() {
      var _this18 = this;
      return this.visibleOptions.filter(function (option) {
        return !_this18.isOptionGroup(option);
      }).length;
    },
    isClearIconVisible: function isClearIconVisible() {
      return (
        this.showClear && this.modelValue != null && isNotEmpty(this.options)
      );
    },
    virtualScrollerDisabled: function virtualScrollerDisabled() {
      return !this.virtualScrollerOptions;
    },
    hasFluid: function hasFluid() {
      return isEmpty(this.fluid) ? !!this.$pcFluid : this.fluid;
    },
  },
  directives: {
    ripple: Ripple,
  },
  components: {
    InputText: InputText,
    VirtualScroller: VirtualScroller,
    Portal: Portal,
    InputIcon: InputIcon,
    IconField: IconField,
    TimesIcon: TimesIcon,
    ChevronDownIcon: ChevronDownIcon,
    SpinnerIcon: SpinnerIcon,
    SearchIcon: SearchIcon,
    CheckIcon: CheckIcon,
    BlankIcon: BlankIcon,
  },
};

var _hoisted_1 = ['id'];
var _hoisted_2 = [
  'id',
  'value',
  'placeholder',
  'tabindex',
  'disabled',
  'aria-label',
  'aria-labelledby',
  'aria-expanded',
  'aria-controls',
  'aria-activedescendant',
  'aria-invalid',
];
var _hoisted_3 = [
  'id',
  'tabindex',
  'aria-label',
  'aria-labelledby',
  'aria-expanded',
  'aria-controls',
  'aria-activedescendant',
  'aria-disabled',
];
var _hoisted_4 = ['id'];
var _hoisted_5 = ['id'];
var _hoisted_6 = [
  'id',
  'aria-label',
  'aria-selected',
  'aria-disabled',
  'aria-setsize',
  'aria-posinset',
  'onClick',
  'onMousemove',
  'data-p-selected',
  'data-p-focused',
  'data-p-disabled',
];
function render(_ctx, _cache, $props, $setup, $data, $options) {
  var _component_SpinnerIcon = resolveComponent('SpinnerIcon');
  var _component_InputText = resolveComponent('InputText');
  var _component_SearchIcon = resolveComponent('SearchIcon');
  var _component_InputIcon = resolveComponent('InputIcon');
  var _component_IconField = resolveComponent('IconField');
  var _component_CheckIcon = resolveComponent('CheckIcon');
  var _component_BlankIcon = resolveComponent('BlankIcon');
  var _component_VirtualScroller = resolveComponent('VirtualScroller');
  var _component_Portal = resolveComponent('Portal');
  var _directive_ripple = resolveDirective('ripple');
  return (
    openBlock(),
    createElementBlock(
      'div',
      mergeProps(
        {
          ref: 'container',
          id: $data.id,
          class: _ctx.cx('root'),
          onClick:
            _cache[11] ||
            (_cache[11] = function () {
              return (
                $options.onContainerClick &&
                $options.onContainerClick.apply($options, arguments)
              );
            }),
        },
        _ctx.ptmi('root')
      ),
      [
        _ctx.editable
          ? (openBlock(),
            createElementBlock(
              'input',
              mergeProps(
                {
                  key: 0,
                  ref: 'focusInput',
                  id: _ctx.labelId || _ctx.inputId,
                  type: 'text',
                  class: [_ctx.cx('label'), _ctx.inputClass, _ctx.labelClass],
                  style: [_ctx.inputStyle, _ctx.labelStyle],
                  value: $options.editableInputValue,
                  placeholder: _ctx.placeholder,
                  tabindex: !_ctx.disabled ? _ctx.tabindex : -1,
                  disabled: _ctx.disabled,
                  autocomplete: 'off',
                  role: 'combobox',
                  'aria-label': _ctx.ariaLabel,
                  'aria-labelledby': _ctx.ariaLabelledby,
                  'aria-haspopup': 'listbox',
                  'aria-expanded': $data.overlayVisible,
                  'aria-controls': $data.id + '_list',
                  'aria-activedescendant': $data.focused
                    ? $options.focusedOptionId
                    : undefined,
                  'aria-invalid': _ctx.invalid || undefined,
                  onFocus:
                    _cache[0] ||
                    (_cache[0] = function () {
                      return (
                        $options.onFocus &&
                        $options.onFocus.apply($options, arguments)
                      );
                    }),
                  onBlur:
                    _cache[1] ||
                    (_cache[1] = function () {
                      return (
                        $options.onBlur &&
                        $options.onBlur.apply($options, arguments)
                      );
                    }),
                  onKeydown:
                    _cache[2] ||
                    (_cache[2] = function () {
                      return (
                        $options.onKeyDown &&
                        $options.onKeyDown.apply($options, arguments)
                      );
                    }),
                  onInput:
                    _cache[3] ||
                    (_cache[3] = function () {
                      return (
                        $options.onEditableInput &&
                        $options.onEditableInput.apply($options, arguments)
                      );
                    }),
                },
                _ctx.ptm('label')
              ),
              null,
              16,
              _hoisted_2
            ))
          : (openBlock(),
            createElementBlock(
              'span',
              mergeProps(
                {
                  key: 1,
                  ref: 'focusInput',
                  id: _ctx.labelId || _ctx.inputId,
                  class: [_ctx.cx('label'), _ctx.inputClass, _ctx.labelClass],
                  style: [_ctx.inputStyle, _ctx.labelStyle],
                  tabindex: !_ctx.disabled ? _ctx.tabindex : -1,
                  role: 'combobox',
                  'aria-label':
                    _ctx.ariaLabel ||
                    ($options.label === 'p-emptylabel'
                      ? undefined
                      : $options.label),
                  'aria-labelledby': _ctx.ariaLabelledby,
                  'aria-haspopup': 'listbox',
                  'aria-expanded': $data.overlayVisible,
                  'aria-controls': $data.id + '_list',
                  'aria-activedescendant': $data.focused
                    ? $options.focusedOptionId
                    : undefined,
                  'aria-disabled': _ctx.disabled,
                  onFocus:
                    _cache[4] ||
                    (_cache[4] = function () {
                      return (
                        $options.onFocus &&
                        $options.onFocus.apply($options, arguments)
                      );
                    }),
                  onBlur:
                    _cache[5] ||
                    (_cache[5] = function () {
                      return (
                        $options.onBlur &&
                        $options.onBlur.apply($options, arguments)
                      );
                    }),
                  onKeydown:
                    _cache[6] ||
                    (_cache[6] = function () {
                      return (
                        $options.onKeyDown &&
                        $options.onKeyDown.apply($options, arguments)
                      );
                    }),
                },
                _ctx.ptm('label')
              ),
              [
                renderSlot(
                  _ctx.$slots,
                  'value',
                  {
                    value: _ctx.modelValue,
                    placeholder: _ctx.placeholder,
                  },
                  function () {
                    var _$options$label;
                    return [
                      createTextVNode(
                        toDisplayString(
                          $options.label === 'p-emptylabel'
                            ? ' '
                            : (_$options$label = $options.label) !== null &&
                                _$options$label !== void 0
                              ? _$options$label
                              : 'empty'
                        ),
                        1
                      ),
                    ];
                  }
                ),
              ],
              16,
              _hoisted_3
            )),
        $options.isClearIconVisible
          ? renderSlot(
              _ctx.$slots,
              'clearicon',
              {
                key: 2,
                class: normalizeClass(_ctx.cx('clearIcon')),
                clearCallback: $options.onClearClick,
              },
              function () {
                return [
                  (openBlock(),
                  createBlock(
                    resolveDynamicComponent(_ctx.clearIcon ? 'i' : 'TimesIcon'),
                    mergeProps(
                      {
                        ref: 'clearIcon',
                        class: [_ctx.cx('clearIcon'), _ctx.clearIcon],
                        onClick: $options.onClearClick,
                      },
                      _ctx.ptm('clearIcon'),
                      {
                        'data-pc-section': 'clearicon',
                      }
                    ),
                    null,
                    16,
                    ['class', 'onClick']
                  )),
                ];
              }
            )
          : createCommentVNode('', true),
        createElementVNode(
          'div',
          mergeProps(
            {
              class: _ctx.cx('dropdown'),
            },
            _ctx.ptm('dropdown')
          ),
          [
            _ctx.loading
              ? renderSlot(
                  _ctx.$slots,
                  'loadingicon',
                  {
                    key: 0,
                    class: normalizeClass(_ctx.cx('loadingIcon')),
                  },
                  function () {
                    return [
                      _ctx.loadingIcon
                        ? (openBlock(),
                          createElementBlock(
                            'span',
                            mergeProps(
                              {
                                key: 0,
                                class: [
                                  _ctx.cx('loadingIcon'),
                                  'pi-spin',
                                  _ctx.loadingIcon,
                                ],
                                'aria-hidden': 'true',
                              },
                              _ctx.ptm('loadingIcon')
                            ),
                            null,
                            16
                          ))
                        : (openBlock(),
                          createBlock(
                            _component_SpinnerIcon,
                            mergeProps(
                              {
                                key: 1,
                                class: _ctx.cx('loadingIcon'),
                                spin: '',
                                'aria-hidden': 'true',
                              },
                              _ctx.ptm('loadingIcon')
                            ),
                            null,
                            16,
                            ['class']
                          )),
                    ];
                  }
                )
              : renderSlot(
                  _ctx.$slots,
                  'dropdownicon',
                  {
                    key: 1,
                    class: normalizeClass(_ctx.cx('dropdownIcon')),
                  },
                  function () {
                    return [
                      (openBlock(),
                      createBlock(
                        resolveDynamicComponent(
                          _ctx.dropdownIcon ? 'span' : 'ChevronDownIcon'
                        ),
                        mergeProps(
                          {
                            class: [_ctx.cx('dropdownIcon'), _ctx.dropdownIcon],
                            'aria-hidden': 'true',
                          },
                          _ctx.ptm('dropdownIcon')
                        ),
                        null,
                        16,
                        ['class']
                      )),
                    ];
                  }
                ),
          ],
          16
        ),
        createVNode(
          _component_Portal,
          {
            appendTo: _ctx.appendTo,
          },
          {
            default: withCtx(function () {
              return [
                createVNode(
                  Transition,
                  mergeProps(
                    {
                      name: 'p-connected-overlay',
                      onEnter: $options.onOverlayEnter,
                      onAfterEnter: $options.onOverlayAfterEnter,
                      onLeave: $options.onOverlayLeave,
                      onAfterLeave: $options.onOverlayAfterLeave,
                    },
                    _ctx.ptm('transition')
                  ),
                  {
                    default: withCtx(function () {
                      return [
                        $data.overlayVisible
                          ? (openBlock(),
                            createElementBlock(
                              'div',
                              mergeProps(
                                {
                                  key: 0,
                                  ref: $options.overlayRef,
                                  class: [
                                    _ctx.cx('overlay'),
                                    _ctx.panelClass,
                                    _ctx.overlayClass,
                                  ],
                                  style: [_ctx.panelStyle, _ctx.overlayStyle],
                                  onClick:
                                    _cache[9] ||
                                    (_cache[9] = function () {
                                      return (
                                        $options.onOverlayClick &&
                                        $options.onOverlayClick.apply(
                                          $options,
                                          arguments
                                        )
                                      );
                                    }),
                                  onKeydown:
                                    _cache[10] ||
                                    (_cache[10] = function () {
                                      return (
                                        $options.onOverlayKeyDown &&
                                        $options.onOverlayKeyDown.apply(
                                          $options,
                                          arguments
                                        )
                                      );
                                    }),
                                },
                                _ctx.ptm('overlay')
                              ),
                              [
                                createElementVNode(
                                  'span',
                                  mergeProps(
                                    {
                                      ref: 'firstHiddenFocusableElementOnOverlay',
                                      role: 'presentation',
                                      'aria-hidden': 'true',
                                      class:
                                        'p-hidden-accessible p-hidden-focusable',
                                      tabindex: 0,
                                      onFocus:
                                        _cache[7] ||
                                        (_cache[7] = function () {
                                          return (
                                            $options.onFirstHiddenFocus &&
                                            $options.onFirstHiddenFocus.apply(
                                              $options,
                                              arguments
                                            )
                                          );
                                        }),
                                    },
                                    _ctx.ptm('hiddenFirstFocusableEl'),
                                    {
                                      'data-p-hidden-accessible': true,
                                      'data-p-hidden-focusable': true,
                                    }
                                  ),
                                  null,
                                  16
                                ),
                                renderSlot(_ctx.$slots, 'header', {
                                  value: _ctx.modelValue,
                                  options: $options.visibleOptions,
                                }),
                                _ctx.filter
                                  ? (openBlock(),
                                    createElementBlock(
                                      'div',
                                      mergeProps(
                                        {
                                          key: 0,
                                          class: _ctx.cx('header'),
                                        },
                                        _ctx.ptm('header')
                                      ),
                                      [
                                        createVNode(
                                          _component_IconField,
                                          {
                                            unstyled: _ctx.unstyled,
                                            pt: _ctx.ptm('pcFilterContainer'),
                                          },
                                          {
                                            default: withCtx(function () {
                                              return [
                                                createVNode(
                                                  _component_InputText,
                                                  {
                                                    ref: 'filterInput',
                                                    type: 'text',
                                                    value: $data.filterValue,
                                                    onVnodeMounted:
                                                      $options.onFilterUpdated,
                                                    onVnodeUpdated:
                                                      $options.onFilterUpdated,
                                                    class: normalizeClass(
                                                      _ctx.cx('pcFilter')
                                                    ),
                                                    placeholder:
                                                      _ctx.filterPlaceholder,
                                                    variant: _ctx.variant,
                                                    unstyled: _ctx.unstyled,
                                                    role: 'searchbox',
                                                    autocomplete: 'off',
                                                    'aria-owns':
                                                      $data.id + '_list',
                                                    'aria-activedescendant':
                                                      $options.focusedOptionId,
                                                    onKeydown:
                                                      $options.onFilterKeyDown,
                                                    onBlur:
                                                      $options.onFilterBlur,
                                                    onInput:
                                                      $options.onFilterChange,
                                                    pt: _ctx.ptm('pcFilter'),
                                                  },
                                                  null,
                                                  8,
                                                  [
                                                    'value',
                                                    'onVnodeMounted',
                                                    'onVnodeUpdated',
                                                    'class',
                                                    'placeholder',
                                                    'variant',
                                                    'unstyled',
                                                    'aria-owns',
                                                    'aria-activedescendant',
                                                    'onKeydown',
                                                    'onBlur',
                                                    'onInput',
                                                    'pt',
                                                  ]
                                                ),
                                                createVNode(
                                                  _component_InputIcon,
                                                  {
                                                    unstyled: _ctx.unstyled,
                                                    pt: _ctx.ptm(
                                                      'pcFilterIconContainer'
                                                    ),
                                                  },
                                                  {
                                                    default: withCtx(
                                                      function () {
                                                        return [
                                                          renderSlot(
                                                            _ctx.$slots,
                                                            'filtericon',
                                                            {},
                                                            function () {
                                                              return [
                                                                _ctx.filterIcon
                                                                  ? (openBlock(),
                                                                    createElementBlock(
                                                                      'span',
                                                                      mergeProps(
                                                                        {
                                                                          key: 0,
                                                                          class:
                                                                            _ctx.filterIcon,
                                                                        },
                                                                        _ctx.ptm(
                                                                          'filterIcon'
                                                                        )
                                                                      ),
                                                                      null,
                                                                      16
                                                                    ))
                                                                  : (openBlock(),
                                                                    createBlock(
                                                                      _component_SearchIcon,
                                                                      normalizeProps(
                                                                        mergeProps(
                                                                          {
                                                                            key: 1,
                                                                          },
                                                                          _ctx.ptm(
                                                                            'filterIcon'
                                                                          )
                                                                        )
                                                                      ),
                                                                      null,
                                                                      16
                                                                    )),
                                                              ];
                                                            }
                                                          ),
                                                        ];
                                                      }
                                                    ),
                                                    _: 3,
                                                  },
                                                  8,
                                                  ['unstyled', 'pt']
                                                ),
                                              ];
                                            }),
                                            _: 3,
                                          },
                                          8,
                                          ['unstyled', 'pt']
                                        ),
                                        createElementVNode(
                                          'span',
                                          mergeProps(
                                            {
                                              role: 'status',
                                              'aria-live': 'polite',
                                              class: 'p-hidden-accessible',
                                            },
                                            _ctx.ptm('hiddenFilterResult'),
                                            {
                                              'data-p-hidden-accessible': true,
                                            }
                                          ),
                                          toDisplayString(
                                            $options.filterResultMessageText
                                          ),
                                          17
                                        ),
                                      ],
                                      16
                                    ))
                                  : createCommentVNode('', true),
                                createElementVNode(
                                  'div',
                                  mergeProps(
                                    {
                                      class: _ctx.cx('listContainer'),
                                      style: {
                                        'max-height':
                                          $options.virtualScrollerDisabled
                                            ? _ctx.scrollHeight
                                            : '',
                                      },
                                    },
                                    _ctx.ptm('listContainer')
                                  ),
                                  [
                                    createVNode(
                                      _component_VirtualScroller,
                                      mergeProps(
                                        {
                                          ref: $options.virtualScrollerRef,
                                        },
                                        _ctx.virtualScrollerOptions,
                                        {
                                          items: $options.visibleOptions,
                                          style: {
                                            height: _ctx.scrollHeight,
                                          },
                                          tabindex: -1,
                                          disabled:
                                            $options.virtualScrollerDisabled,
                                          pt: _ctx.ptm('virtualScroller'),
                                        }
                                      ),
                                      createSlots(
                                        {
                                          content: withCtx(function (_ref) {
                                            var styleClass = _ref.styleClass,
                                              contentRef = _ref.contentRef,
                                              items = _ref.items,
                                              getItemOptions =
                                                _ref.getItemOptions,
                                              contentStyle = _ref.contentStyle,
                                              itemSize = _ref.itemSize;
                                            return [
                                              createElementVNode(
                                                'ul',
                                                mergeProps(
                                                  {
                                                    ref: function ref(el) {
                                                      return $options.listRef(
                                                        el,
                                                        contentRef
                                                      );
                                                    },
                                                    id: $data.id + '_list',
                                                    class: [
                                                      _ctx.cx('list'),
                                                      styleClass,
                                                    ],
                                                    style: contentStyle,
                                                    role: 'listbox',
                                                  },
                                                  _ctx.ptm('list')
                                                ),
                                                [
                                                  (openBlock(true),
                                                  createElementBlock(
                                                    Fragment,
                                                    null,
                                                    renderList(
                                                      items,
                                                      function (option, i) {
                                                        return (
                                                          openBlock(),
                                                          createElementBlock(
                                                            Fragment,
                                                            {
                                                              key: $options.getOptionRenderKey(
                                                                option,
                                                                $options.getOptionIndex(
                                                                  i,
                                                                  getItemOptions
                                                                )
                                                              ),
                                                            },
                                                            [
                                                              $options.isOptionGroup(
                                                                option
                                                              )
                                                                ? (openBlock(),
                                                                  createElementBlock(
                                                                    'li',
                                                                    mergeProps(
                                                                      {
                                                                        key: 0,
                                                                        id:
                                                                          $data.id +
                                                                          '_' +
                                                                          $options.getOptionIndex(
                                                                            i,
                                                                            getItemOptions
                                                                          ),
                                                                        style: {
                                                                          height:
                                                                            itemSize
                                                                              ? itemSize +
                                                                                'px'
                                                                              : undefined,
                                                                        },
                                                                        class:
                                                                          _ctx.cx(
                                                                            'optionGroup'
                                                                          ),
                                                                        role: 'option',
                                                                        ref_for: true,
                                                                      },
                                                                      _ctx.ptm(
                                                                        'optionGroup'
                                                                      )
                                                                    ),
                                                                    [
                                                                      renderSlot(
                                                                        _ctx.$slots,
                                                                        'optiongroup',
                                                                        {
                                                                          option:
                                                                            option.optionGroup,
                                                                          index:
                                                                            $options.getOptionIndex(
                                                                              i,
                                                                              getItemOptions
                                                                            ),
                                                                        },
                                                                        function () {
                                                                          return [
                                                                            createElementVNode(
                                                                              'span',
                                                                              mergeProps(
                                                                                {
                                                                                  class:
                                                                                    _ctx.cx(
                                                                                      'optionGroupLabel'
                                                                                    ),
                                                                                  ref_for: true,
                                                                                },
                                                                                _ctx.ptm(
                                                                                  'optionGroupLabel'
                                                                                )
                                                                              ),
                                                                              toDisplayString(
                                                                                $options.getOptionGroupLabel(
                                                                                  option.optionGroup
                                                                                )
                                                                              ),
                                                                              17
                                                                            ),
                                                                          ];
                                                                        }
                                                                      ),
                                                                    ],
                                                                    16,
                                                                    _hoisted_5
                                                                  ))
                                                                : withDirectives(
                                                                    (openBlock(),
                                                                    createElementBlock(
                                                                      'li',
                                                                      mergeProps(
                                                                        {
                                                                          key: 1,
                                                                          id:
                                                                            $data.id +
                                                                            '_' +
                                                                            $options.getOptionIndex(
                                                                              i,
                                                                              getItemOptions
                                                                            ),
                                                                          class:
                                                                            _ctx.cx(
                                                                              'option',
                                                                              {
                                                                                option:
                                                                                  option,
                                                                                focusedOption:
                                                                                  $options.getOptionIndex(
                                                                                    i,
                                                                                    getItemOptions
                                                                                  ),
                                                                              }
                                                                            ),
                                                                          style:
                                                                            {
                                                                              height:
                                                                                itemSize
                                                                                  ? itemSize +
                                                                                    'px'
                                                                                  : undefined,
                                                                            },
                                                                          role: 'option',
                                                                          'aria-label':
                                                                            $options.getOptionLabel(
                                                                              option
                                                                            ),
                                                                          'aria-selected':
                                                                            $options.isSelected(
                                                                              option
                                                                            ),
                                                                          'aria-disabled':
                                                                            $options.isOptionDisabled(
                                                                              option
                                                                            ),
                                                                          'aria-setsize':
                                                                            $options.ariaSetSize,
                                                                          'aria-posinset':
                                                                            $options.getAriaPosInset(
                                                                              $options.getOptionIndex(
                                                                                i,
                                                                                getItemOptions
                                                                              )
                                                                            ),
                                                                          onClick:
                                                                            function onClick(
                                                                              $event
                                                                            ) {
                                                                              return $options.onOptionSelect(
                                                                                $event,
                                                                                option
                                                                              );
                                                                            },
                                                                          onMousemove:
                                                                            function onMousemove(
                                                                              $event
                                                                            ) {
                                                                              return $options.onOptionMouseMove(
                                                                                $event,
                                                                                $options.getOptionIndex(
                                                                                  i,
                                                                                  getItemOptions
                                                                                )
                                                                              );
                                                                            },
                                                                          'data-p-selected':
                                                                            $options.isSelected(
                                                                              option
                                                                            ),
                                                                          'data-p-focused':
                                                                            $data.focusedOptionIndex ===
                                                                            $options.getOptionIndex(
                                                                              i,
                                                                              getItemOptions
                                                                            ),
                                                                          'data-p-disabled':
                                                                            $options.isOptionDisabled(
                                                                              option
                                                                            ),
                                                                          ref_for: true,
                                                                        },
                                                                        $options.getPTItemOptions(
                                                                          option,
                                                                          getItemOptions,
                                                                          i,
                                                                          'option'
                                                                        )
                                                                      ),
                                                                      [
                                                                        _ctx.checkmark
                                                                          ? (openBlock(),
                                                                            createElementBlock(
                                                                              Fragment,
                                                                              {
                                                                                key: 0,
                                                                              },
                                                                              [
                                                                                $options.isSelected(
                                                                                  option
                                                                                )
                                                                                  ? (openBlock(),
                                                                                    createBlock(
                                                                                      _component_CheckIcon,
                                                                                      mergeProps(
                                                                                        {
                                                                                          key: 0,
                                                                                          class:
                                                                                            _ctx.cx(
                                                                                              'optionCheckIcon'
                                                                                            ),
                                                                                          ref_for: true,
                                                                                        },
                                                                                        _ctx.ptm(
                                                                                          'optionCheckIcon'
                                                                                        )
                                                                                      ),
                                                                                      null,
                                                                                      16,
                                                                                      [
                                                                                        'class',
                                                                                      ]
                                                                                    ))
                                                                                  : (openBlock(),
                                                                                    createBlock(
                                                                                      _component_BlankIcon,
                                                                                      mergeProps(
                                                                                        {
                                                                                          key: 1,
                                                                                          class:
                                                                                            _ctx.cx(
                                                                                              'optionBlankIcon'
                                                                                            ),
                                                                                          ref_for: true,
                                                                                        },
                                                                                        _ctx.ptm(
                                                                                          'optionBlankIcon'
                                                                                        )
                                                                                      ),
                                                                                      null,
                                                                                      16,
                                                                                      [
                                                                                        'class',
                                                                                      ]
                                                                                    )),
                                                                              ],
                                                                              64
                                                                            ))
                                                                          : createCommentVNode(
                                                                              '',
                                                                              true
                                                                            ),
                                                                        renderSlot(
                                                                          _ctx.$slots,
                                                                          'option',
                                                                          {
                                                                            option:
                                                                              option,
                                                                            selected:
                                                                              $options.isSelected(
                                                                                option
                                                                              ),
                                                                            index:
                                                                              $options.getOptionIndex(
                                                                                i,
                                                                                getItemOptions
                                                                              ),
                                                                          },
                                                                          function () {
                                                                            return [
                                                                              createElementVNode(
                                                                                'span',
                                                                                mergeProps(
                                                                                  {
                                                                                    class:
                                                                                      _ctx.cx(
                                                                                        'optionLabel'
                                                                                      ),
                                                                                    ref_for: true,
                                                                                  },
                                                                                  _ctx.ptm(
                                                                                    'optionLabel'
                                                                                  )
                                                                                ),
                                                                                toDisplayString(
                                                                                  $options.getOptionLabel(
                                                                                    option
                                                                                  )
                                                                                ),
                                                                                17
                                                                              ),
                                                                            ];
                                                                          }
                                                                        ),
                                                                      ],
                                                                      16,
                                                                      _hoisted_6
                                                                    )),
                                                                    [
                                                                      [
                                                                        _directive_ripple,
                                                                      ],
                                                                    ]
                                                                  ),
                                                            ],
                                                            64
                                                          )
                                                        );
                                                      }
                                                    ),
                                                    128
                                                  )),
                                                  $data.filterValue &&
                                                  (!items ||
                                                    (items &&
                                                      items.length === 0))
                                                    ? (openBlock(),
                                                      createElementBlock(
                                                        'li',
                                                        mergeProps(
                                                          {
                                                            key: 0,
                                                            class:
                                                              _ctx.cx(
                                                                'emptyMessage'
                                                              ),
                                                            role: 'option',
                                                          },
                                                          _ctx.ptm(
                                                            'emptyMessage'
                                                          ),
                                                          {
                                                            'data-p-hidden-accessible': true,
                                                          }
                                                        ),
                                                        [
                                                          renderSlot(
                                                            _ctx.$slots,
                                                            'emptyfilter',
                                                            {},
                                                            function () {
                                                              return [
                                                                createTextVNode(
                                                                  toDisplayString(
                                                                    $options.emptyFilterMessageText
                                                                  ),
                                                                  1
                                                                ),
                                                              ];
                                                            }
                                                          ),
                                                        ],
                                                        16
                                                      ))
                                                    : !_ctx.options ||
                                                        (_ctx.options &&
                                                          _ctx.options
                                                            .length === 0)
                                                      ? (openBlock(),
                                                        createElementBlock(
                                                          'li',
                                                          mergeProps(
                                                            {
                                                              key: 1,
                                                              class:
                                                                _ctx.cx(
                                                                  'emptyMessage'
                                                                ),
                                                              role: 'option',
                                                            },
                                                            _ctx.ptm(
                                                              'emptyMessage'
                                                            ),
                                                            {
                                                              'data-p-hidden-accessible': true,
                                                            }
                                                          ),
                                                          [
                                                            renderSlot(
                                                              _ctx.$slots,
                                                              'empty',
                                                              {},
                                                              function () {
                                                                return [
                                                                  createTextVNode(
                                                                    toDisplayString(
                                                                      $options.emptyMessageText
                                                                    ),
                                                                    1
                                                                  ),
                                                                ];
                                                              }
                                                            ),
                                                          ],
                                                          16
                                                        ))
                                                      : createCommentVNode(
                                                          '',
                                                          true
                                                        ),
                                                ],
                                                16,
                                                _hoisted_4
                                              ),
                                            ];
                                          }),
                                          _: 2,
                                        },
                                        [
                                          _ctx.$slots.loader
                                            ? {
                                                name: 'loader',
                                                fn: withCtx(function (_ref2) {
                                                  var options = _ref2.options;
                                                  return [
                                                    renderSlot(
                                                      _ctx.$slots,
                                                      'loader',
                                                      {
                                                        options: options,
                                                      }
                                                    ),
                                                  ];
                                                }),
                                                key: '0',
                                              }
                                            : undefined,
                                        ]
                                      ),
                                      1040,
                                      ['items', 'style', 'disabled', 'pt']
                                    ),
                                  ],
                                  16
                                ),
                                renderSlot(_ctx.$slots, 'footer', {
                                  value: _ctx.modelValue,
                                  options: $options.visibleOptions,
                                }),
                                !_ctx.options ||
                                (_ctx.options && _ctx.options.length === 0)
                                  ? (openBlock(),
                                    createElementBlock(
                                      'span',
                                      mergeProps(
                                        {
                                          key: 1,
                                          role: 'status',
                                          'aria-live': 'polite',
                                          class: 'p-hidden-accessible',
                                        },
                                        _ctx.ptm('hiddenEmptyMessage'),
                                        {
                                          'data-p-hidden-accessible': true,
                                        }
                                      ),
                                      toDisplayString(
                                        $options.emptyMessageText
                                      ),
                                      17
                                    ))
                                  : createCommentVNode('', true),
                                createElementVNode(
                                  'span',
                                  mergeProps(
                                    {
                                      role: 'status',
                                      'aria-live': 'polite',
                                      class: 'p-hidden-accessible',
                                    },
                                    _ctx.ptm('hiddenSelectedMessage'),
                                    {
                                      'data-p-hidden-accessible': true,
                                    }
                                  ),
                                  toDisplayString($options.selectedMessageText),
                                  17
                                ),
                                createElementVNode(
                                  'span',
                                  mergeProps(
                                    {
                                      ref: 'lastHiddenFocusableElementOnOverlay',
                                      role: 'presentation',
                                      'aria-hidden': 'true',
                                      class:
                                        'p-hidden-accessible p-hidden-focusable',
                                      tabindex: 0,
                                      onFocus:
                                        _cache[8] ||
                                        (_cache[8] = function () {
                                          return (
                                            $options.onLastHiddenFocus &&
                                            $options.onLastHiddenFocus.apply(
                                              $options,
                                              arguments
                                            )
                                          );
                                        }),
                                    },
                                    _ctx.ptm('hiddenLastFocusableEl'),
                                    {
                                      'data-p-hidden-accessible': true,
                                      'data-p-hidden-focusable': true,
                                    }
                                  ),
                                  null,
                                  16
                                ),
                              ],
                              16
                            ))
                          : createCommentVNode('', true),
                      ];
                    }),
                    _: 3,
                  },
                  16,
                  ['onEnter', 'onAfterEnter', 'onLeave', 'onAfterLeave']
                ),
              ];
            }),
            _: 3,
          },
          8,
          ['appendTo']
        ),
      ],
      16,
      _hoisted_1
    )
  );
}

script.render = render;

export { script as default };
//# sourceMappingURL=index.mjs.map
