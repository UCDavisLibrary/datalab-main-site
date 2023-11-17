import { LitElement } from 'lit';
import * as templates from "./ucdlib-dl-jobs-board-admin.tpl.js";

import WpRest from '../../controllers/wp-rest.js';
import { WaitController } from '@ucd-lib/theme-elements/utils/controllers/index.js';

import '@ucd-lib/theme-elements/ucdlib/ucdlib-pages/ucdlib-pages.js';
import '@ucd-lib/theme-elements/brand/ucd-theme-slim-select/ucd-theme-slim-select.js';

export default class UcdlibDlJobsBoardAdmin extends LitElement {

  static get properties() {
    return {
      wpNonce: {type: String, attribute: 'wp-nonce'},
      restNamespace: {type: String, attribute: 'rest-namespace'},
      logoUrl: {type: String, attribute: 'logo-url'},
      logoWidth: {type: String, attribute: 'logo-width'},
      pages: {state: true},
      page: {state: true},
      successMessage: {state: true},
      loadingHeight: {state: true},
    }
  }

  static get styles() {
    return templates.styles();
  }

  constructor() {
    super();
    this.render = templates.render.bind(this);
    this.renderPendingRequests = templates.renderPendingRequests.bind(this);
    this.renderActiveListings = templates.renderActiveListings.bind(this);
    this.renderExpiredListings = templates.renderExpiredListings.bind(this);
    this.renderSettings = templates.renderSettings.bind(this);
    this.renderLoading = templates.renderLoading.bind(this);
    this.renderError = templates.renderError.bind(this);

    this.api = new WpRest(this);
    this.wait = new WaitController(this);

    this.successMessage = {
      show: false,
      message: ''
    }
    this.wpNonce = '';
    this.restNamespace = '';
    this.logoUrl = '';
    this.logoWidth = '';
    this.loadingHeight = 'auto';

    this.pages = [
      {
        id: 'pending',
        name: 'Pending Requests',
        render: this.renderPendingRequests,
        getData: this.getPendingRequests,
      },
      {
        id: 'active',
        name: 'Active Listings',
        render: this.renderActiveListings
      },
      {
        id: 'expired',
        name: 'Expired Listings',
        render: this.renderExpiredListings
      },
      {
        id: 'settings',
        name: 'Settings',
        render: this.renderSettings,
        getData: this.getSettings,
        data: this.getPageDataTemplate('settings')
      },
      {
        id: 'loading',
        name: 'Loading',
        render: this.renderLoading,
        noNav: true
      },
      {
        id: 'error',
        name: 'Error',
        render: this.renderError,
        noNav: true
      }
    ];
    this.page = 'loading';

    this.apiEndpoints = {
      settings: 'admin-settings',
      formFields: 'form-fields',
      submissions: 'submissions'
    }

    this.formFields = [
      {id: 'job-title', name: 'Job Title', settingsProp: 'jobTitle'},
      {id: 'listing-end-date', name: 'Listing End Date', settingsProp: 'listingEndDate'}
    ]
  }

  firstUpdated(){
    this._onPageChange(0);
  }

  async getPendingRequests(){
    const d = await this.api.get(this.apiEndpoints.submissions + '/pending');
    return d;
  }

  /**
   * @description Returns the data template for the specified page
   * @param {String} page - Page id
   * @returns
   */
  getPageDataTemplate(page){
    if ( !page ) return {};
    if ( page === 'pending' ){
      return {
        totalCt: 0,
        pagedSubmissions: []
      }
    }
    if ( page === 'settings' ){
      return {
        forms: [],
        selectedForm: '',
        selectedFormFields: {},
        users: [],
        addBoardManagers: [],
        removeBoardManagers: [],
        formFields: []
      }
    }
  }

  /**
   * @description Changes the page to the one specified by the index
   * Retrieves data for page if necessary
   * @param {Number} i - The index of the page to change to
   * @returns
   */
  async _onPageChange(i) {
    const page = this.pages[i];
    if ( page.getData ) {
      this._showLoading();
      const data = await page.getData.call(this);
      if ( data.status === 'error' ) {
        this.page = 'error';
        return;
      }
      page.data = {...page.data, ...data.data};
    }
    this.page = this.pages[i].id;
    this.requestUpdate();
  }

  /**
   * @description Retrieves the admin settings for the jobs board
   * @returns {Object}
   */
  async getSettings(){
    const d = await this.api.get(this.apiEndpoints.settings);
    this.settingsCacheKey = d.cacheKey;
    return d;
  }

  /**
   * @description Clears the local cache for the admin settings
   */
  clearSettingsCache(){
    if ( this.settingsCacheKey ){
      this.api.clearCache(this.settingsCacheKey);
    }
  }

  /**
   * @description Handles the event fired when the settings form is submitted
   * @param {*} e
   */
  async _onSettingsSubmit(e){
    const id = 'settings';
    e.preventDefault();
    const page = this.pages.find(p => p.id === id);

    // remove props we dont need to send to server
    const dropProps = ['forms', 'users', 'formFields'];
    const payload = {...page.data};
    dropProps.forEach(prop => delete payload[prop]);

    this._showLoading();
    const data = await this.api.post(this.apiEndpoints.settings, payload);
    if ( data.status === 'error' ) {
      this.page = 'error';
      return;
    }
    page.data = {...this.getPageDataTemplate(id), ...data.data};
    this.page = id;
    this.clearSettingsCache();
    this.requestUpdate();
    this.showSuccessMessage('Settings saved');

  }

  /**
   * @description Will show a success message at the top of the page
   * @param {String} message - The message to show
   * @returns
   */
  async showSuccessMessage(message){
    if ( !message ) return;
    if ( this.successMessage.show ) return;
    this.successMessage = {
      show: true,
      message
    }
    window.scrollTo({
      top: 0,
      left: 0,
      behavior: 'smooth'
    });

    // hide message after 5 seconds
    await this.wait.wait(5000);
    this.successMessage = {
      show: false,
      message: ''
    }
  }

  /**
   * @description Handles the event fired when a page data input is changed
   * @param {String} pageId - The id of the page that the input belongs to
   * @param {String} key - The key of the page's data object to update
   * @param {*} value - The value to update the data object with
   */
  _onPageDataInput(pageId, key, value){
    const page = this.pages.find(p => p.id === pageId);
    page.data[key] = value;
    this.requestUpdate();
  }

  _onSettingsFormFieldSelect(field, value){
    const page = this.pages.find(p => p.id === 'settings');
    page.data.selectedFormFields[field] = value;
    this.requestUpdate();
  }

  /**
   * @description Handles the event when the submission form select is changed on the settings page
   * @param {*} e
   * @returns
   */
  async _onSettingsFormSelect(e){
    const formId = e.target.value;

    // get form fields for selected form
    const d = await this.api.get(`${this.apiEndpoints.formFields}/${formId}`);
    if ( d.status === 'error' ) {
      this.page = 'error';
      return;
    }
    this._onPageDataInput('settings', 'selectedForm', formId)
    this._onPageDataInput('settings', 'formFields', d.data);
  }

  /**
   * @description Handles the event fired when a board manager remove checkbox is toggled
   * @param {String} userId - The wp id of the user that was toggled
   * @returns
   */
  _onManagerRemoveToggle(userId){
    userId = parseInt(userId);
    if ( !userId ) return;
    const page = this.pages.find(p => p.id === 'settings');
    const index = page.data.removeBoardManagers.indexOf(userId);
    if ( index > -1 ) {
      page.data.removeBoardManagers.splice(index, 1);
    } else {
      page.data.removeBoardManagers.push(userId);
    }
    this.requestUpdate();
  }

  /**
   * @description Shows the loading page
   * Sets loading height to try to prevent page from jumping
   */
  _showLoading(){
    const pagesEle = this.renderRoot.querySelector('ucdlib-pages');
    this.loadingHeight = pagesEle ? pagesEle.offsetHeight + 'px' : 'auto';
    this.page = 'loading';
  }
}

customElements.define('ucdlib-dl-jobs-board-admin', UcdlibDlJobsBoardAdmin);
