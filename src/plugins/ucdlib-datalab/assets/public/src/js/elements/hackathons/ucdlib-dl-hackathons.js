import { LitElement } from 'lit';
import {render, styles} from "./ucdlib-dl-hackathons.tpl.js";

import { MutationObserverController, WaitController } from "@ucd-lib/theme-elements/utils/controllers/index.js";
import ElementStatusController from "../../controllers/element-status.js";
import WpRest from '../../controllers/wp-rest.js';

export default class UcdlibDlHackathons extends LitElement {

  static get properties() {
    return {
      typeFilters: {type: Array},
      yearFilters: {type: Array},
      selectedType: {type: String},
      selectedYear: {type: String},
      orderBy: {type: String},
      currentPage: {type: Number},
      SsrPropertiesLoaded: {state: true},
      status: {state: true},
      results: {state: true},
      totalPages: {state: true},
      errorMessage: {state: true},
      defaultImage: {state: true}
    }
  }

  static get styles() {
    return styles();
  }

  constructor() {
    super();
    this.render = render.bind(this);

    this.SsrPropertiesLoaded = false;
    this.status = 'loading';
    this.results = [];
    this.typeFilters = [];
    this.yearFilters = [];
    this.selectedType = '';
    this.selectedYear = '';
    this.orderBy = '';
    this.currentPage = 1;
    this.totalPages = 1;

    // controllers
    new MutationObserverController(this, {childList: true, subtree: false}, '_onScriptLoaded');
    this.api = new WpRest(this);
    this.wait = new WaitController(this);
    this.statusController = new ElementStatusController(this, {
      loadingHeight: 'auto',
      errorMessage: 'An unexpected error occurred.',
      noResultsGeneralMessage: 'No results found.',
      noResultsSpecificMessage: 'Please refine your search criteria and try again.'
    });

    // set initial state from url
    this.urlParamMap = [
      {prop: 'selectedType', param: 'type'},
      {prop: 'selectedYear', param: 'start-year'},
      {prop: 'orderBy', param: 'orderby'},
      {prop: 'currentPage', param: 'page', default: 1}
    ];
    this.setPropsFromUrl();
  }

  async search(){
    this.replaceUrlState();
    this.status = 'loading';
    const args = {};
    for (let m of this.urlParamMap) {
      if (this[m.prop]) args[m.param] = this[m.prop];
    }
    const response = await this.api.get('search-past', args);
    if ( response.status !== 'success' ) {
      console.error(response.error);
      this.status = 'error';
      this.errorMessage = response?.error?.message || '';
      return;
    }

    this.totalPages = response.data.totalPageCt;
    this.results = response.data.results;

    this.status = 'loaded';
    await this._setLoadingHeight();
  }

  /**
   * @method _onScriptLoaded
   * @description Callback for mutation observer controller. Loads properties from json script tag.
   */
  _onScriptLoaded() {
    if ( this.SsrPropertiesLoaded ) return;
    const scriptEle = this.querySelector('script[type="application/json"]');
    if ( scriptEle ) {
      try {
        const props = JSON.parse(scriptEle.innerHTML);
        for( let key in props ) {
          this[key] = props[key];
        }
        this.SsrPropertiesLoaded = true;
        this.search();
      } catch(e) {
        console.error('Failed to parse SSR properties', e);
      }
    }
  }

  /**
   * @method setPropsFromUrl
   * @description Set element properties from url query params
   */
  setPropsFromUrl() {
    let url = new URL(window.location.href);
    for( let i = 0; i < this.urlParamMap.length; i++ ) {
      let map = this.urlParamMap[i];
      if( url.searchParams.has(map.param) ) {
        this[map.prop] = url.searchParams.get(map.param);
      }
    }
  }

  /**
   * @method replaceUrlState
   * @description Replace url query params with current element properties
   */
  replaceUrlState() {
    let url = new URL(window.location.href);
    for( let i = 0; i < this.urlParamMap.length; i++ ) {
      let map = this.urlParamMap[i];
      if( this[map.prop] && this[map.prop] !== map.default) {
        url.searchParams.set(map.param, this[map.prop]);
      } else {
        url.searchParams.delete(map.param);
      }
    }
    window.history.replaceState({}, '', url);
  }

  /**
   * @method _onSubmit
   * @description Handle search form submit event
   */
  _onSubmit(e) {
    e.preventDefault();
    this.search();
  }

  _onFieldInput(prop, value) {
    if ( prop !== 'currentPage' ) {
      this.currentPage = 1;
    }
    this[prop] = value;
    this.search();
  }

  /**
   * @description Set the height of the loading container to the height of the loaded content
   */
  async _setLoadingHeight(){
    await this.wait.waitForUpdate();
    await this.wait.waitForFrames(3);
    const container = this.renderRoot.querySelector('#loaded');
    if ( !container ) return;
    const height = container.offsetHeight;
    this.statusController.setLoadingHeight(`${height}px`);
  }

}

customElements.define('ucdlib-dl-hackathons', UcdlibDlHackathons);
