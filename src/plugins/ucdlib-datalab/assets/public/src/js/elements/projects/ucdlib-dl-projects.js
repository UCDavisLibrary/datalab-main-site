import { LitElement } from 'lit';
import {render, styles} from "./ucdlib-dl-projects.tpl.js";

import { MutationObserverController, WaitController } from "@ucd-lib/theme-elements/utils/controllers/index.js";
import ElementStatusController from "../../controllers/element-status.js";
import WpRest from '../../controllers/wp-rest.js';

/**
 * @class UcdlibDlProjects
 * @description Custom element for displaying a filterable list of datalab projects
 */
export default class UcdlibDlProjects extends LitElement {

  static get properties() {
    return {
      approachFilters: {type: Array},
      statusFilters: {type: Array},
      themeFilters: {type: Array},
      selectedApproach: {type: String},
      selectedStatus: {type: String},
      selectedTheme: {type: String},
      defaultImage: {type: String},
      totalPages: { state: true },
      orderBy: {type: String},
      SsrPropertiesLoaded: {state: true},
      status: {state: true},
      projects: {state: true},
      errorMessage: {state: true}
    }
  }

  static get styles() {
    return styles();
  }

  constructor() {
    super();
    this.render = render.bind(this);

    this.approachFilters = [];
    this.statusFilters = [];
    this.themeFilters = [];
    this.selectedApproach = '';
    this.selectedStatus = '';
    this.selectedTheme = '';
    this.orderBy = '';
    this.currentPage = 1;
    this.totalPages = 1;
    this.projects = [];
    this.errorMessage = '';
    this.defaultImage = '';

    this.SsrPropertiesLoaded = false;
    this.status = 'loading';

    this.urlParamMap = [
      {prop: 'selectedApproach', param: 'approach'},
      {prop: 'selectedStatus', param: 'status'},
      {prop: 'selectedTheme', param: 'theme'},
      {prop: 'orderBy', param: 'orderby'},
      {prop: 'currentPage', param: 'page', default: 1}
    ];

    // controllers
    new MutationObserverController(this, {childList: true, subtree: false}, '_onScriptLoaded');
    this.api = new WpRest(this);
    this.wait = new WaitController(this);
    this.statusController = new ElementStatusController(this, {
      loadingHeight: 'auto',
      errorMessage: 'An unexpected error occurred.',
      noResultsGeneralMessage: 'No projects found.',
      noResultsSpecificMessage: 'Please refine your search criteria and try again.'
    });

    this.setPropsFromUrl();
  }

  /**
   * @description Performs API request to get projects based on current element properties
   */
  async search(){
    this.status = 'loading';
    this.replaceUrlState();
    const args = {};
    for (let m of this.urlParamMap) {
      if (this[m.prop]) args[m.param] = this[m.prop];
    }
    const response = await this.api.get('search', args);
    if ( response.status !== 'success' ) {
      console.error(response.error);
      this.status = 'error';
      this.errorMessage = response?.error?.message || '';
      return;
    }

    this.totalPages = response.data.totalPageCt;
    this.projects = response.data.results;

    this.status = 'loaded';
    await this._setLoadingHeight();
  }

  /**
   * @description Check if the search form is in the viewport
   * @returns {Boolean} - True if the form is in the viewport
   */
  formIsInViewport() {
    const form = this.renderRoot.querySelector('form');
    if ( !form ) return false;
    const rect = form.getBoundingClientRect();
    return (
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  /**
   * @description Scroll to the search form (aka the top of the element)
   */
  scrollToForm() {
    const form = this.renderRoot.querySelector('form');
    if ( form ) {
      form.scrollIntoView({behavior: 'smooth'});
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
   * @description Get the image source for a project
   * @param {Object} project - Project object from API
   * @returns {String} - Image source
   */
  projectImage(project){
    if ( project.imageSrc ) return project.imageSrc;
    return this.defaultImage;
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

  /**
   * @method _onSubmit
   * @description Handle search form submit event
   */
  _onSubmit(e) {
    e.preventDefault();
    this.search();
  }

  /**
   * @method _onFieldInput
   * @description Handle filter select change event
   * @param {String} prop property to update
   * @param {String} value new value
   */
  async _onFieldInput(prop, value) {
    if ( prop !== 'currentPage' ) {
      this.currentPage = 1;
    }
    this[prop] = value;
    await this.search();
    await this.updateComplete;
    if ( !this.formIsInViewport() ) {
      this.scrollToForm();
    }
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
}

customElements.define('ucdlib-dl-projects', UcdlibDlProjects);
