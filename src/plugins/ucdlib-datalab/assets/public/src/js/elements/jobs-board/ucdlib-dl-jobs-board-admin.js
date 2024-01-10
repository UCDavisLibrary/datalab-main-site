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
      pageHistory: {state: true},
    }
  }

  static get styles() {
    return templates.styles();
  }

  constructor() {
    super();

    // bind templates
    this.render = templates.render.bind(this);
    this.renderPendingRequests = templates.renderPendingRequests.bind(this);
    this.renderActiveListings = templates.renderActiveListings.bind(this);
    this.renderExpiredListings = templates.renderExpiredListings.bind(this);
    this.renderSettings = templates.renderSettings.bind(this);
    this.renderLoading = templates.renderLoading.bind(this);
    this.renderError = templates.renderError.bind(this);
    this.renderJobListingDetail = templates.renderJobListingDetail.bind(this);

    // controllers
    this.api = new WpRest(this);
    this.wait = new WaitController(this);

    // init state
    this.successMessage = {
      show: false,
      message: ''
    }
    this.wpNonce = '';
    this.restNamespace = '';
    this.logoUrl = '';
    this.logoWidth = '';
    this.loadingHeight = 'auto';
    this.apiEndpoints = {
      settings: 'admin-settings',
      formFields: 'form-fields',
      submissions: 'submissions',
      updateSubmissionMeta: 'update-meta',
      statusCheck: 'run-status-check'
    }
    this.formFields = [
      {id: 'job-title', name: 'Job Title', settingsProp: 'jobTitle'},
      {id: 'listing-end-date', name: 'Listing End Date', settingsProp: 'listingEndDate'},
      {id: 'employer', name: 'Employer', settingsProp: 'employer'}
    ];
    this.pageHistory = [];

    // register pages
    this.pages = [
      {
        id: 'pending',
        name: 'Pending Requests',
        render: this.renderPendingRequests,
        hasListings: true,
        getData: [this.getListings, {id: 'pending'}]
      },
      {
        id: 'active',
        name: 'Active Listings',
        render: this.renderActiveListings,
        hasListings: true,
        getData: [this.getListings, {id: 'active'}]
      },
      {
        id: 'expired',
        name: 'Expired Listings',
        render: this.renderExpiredListings,
        hasListings: true,
        getData: [this.getListings, {id: 'expired'}]
      },
      {
        id: 'settings',
        name: 'Settings',
        render: this.renderSettings,
        getData: this.getSettings
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
      },
      {
        id: 'detail',
        name: 'Job Detail',
        render: this.renderJobListingDetail,
        noNav: true
      }
    ];
    this.pages.forEach(page => {
      page.data = this.getPageDataTemplate(page.id);
      page.cacheKeys = [];
    });
    this.page = 'loading';
  }

  firstUpdated(){
    this._onPageChange(0);
  }

  /**
   * @description Returns the data template for the specified page
   * @param {String} page - Page id
   * @returns
   */
  getPageDataTemplate(page){
    if ( page === 'pending' || page === 'active' || page === 'expired' ){
      const d = {
        totalCt: 0,
        totalPageCt: 0,
        page: 1,
        pagedSubmissions: [],
        formFields: [],
        actions: {},
        assignedFormFields: {}
      };
      if ( page === 'pending' ) {
        d.actions.approve = [];
        d.actions.deny = [];
      } else if ( page === 'active' ) {
        d.actions.expire = [];
        d.actions.revertToPending = [];
        d.actions.delete = [];
      } else if ( page === 'expired' ) {
        d.actions.revertToPending = [];
        d.actions.delete = [];
      }
      return d;
    }
    if ( page === 'settings' ){
      return {
        forms: [],
        selectedForm: '',
        selectedFormFields: {},
        publicFieldDisplayOrder: {},
        users: [],
        addBoardManagers: [],
        removeBoardManagers: [],
        formFields: [],
        cronStatus: '',
      }
    }
    if ( page === 'detail' ){
      return {
        submission: {},
        formData: [],
        updatedFormData: {}
      }
    }
    return {};
  }

  /**
   * @description Returns the brief form field object for the specified field id
   * @param {String} fieldId - The id of the field to retrieve
   * @returns {Object} - The field object {id, label, type}.
   * All properties will be empty strings if field is not found
   */
  getFieldObject(fieldId){
    for ( const page of this.pages ){
      if ( !page.hasListings ) continue;
      if ( !page.data?.formFields ) continue;
      const field = page.data.formFields.find(f => f.id === fieldId);
      if ( field ) return field;
    }
    return { id: '', label: '', type: ''}
  }

  /**
   * @description Changes the page to the one specified by the index
   * Retrieves data for page if necessary
   * @param {Number} i - The index of the page to change to
   * @param {String} id - The id of the page to change to
   * @returns
   */
  async _onPageChange(i, id) {
    let page;
    if ( i !== undefined ) {
      page = this.pages[i];
    } else if ( id ) {
      page = this.pages.find(p => p.id === id);
    } else {
      return;
    }
    if ( !page ) return;

    this.addToPageHistory();

    if ( page.getData ) {
      this._showLoading();
      let data;
      if ( Array.isArray(page.getData) ) {
        data = await page.getData[0].call(this, page.getData[1]);
      } else {
        data = await page.getData.call(this);
      }
      if ( data.status === 'error' ) {
        this.page = 'error';
        return;
      }
      page.data = {...page.data, ...data.data};
    }
    this.page = page.id;
    this.requestUpdate();
  }

  /**
   * @description Handles event when an individual job board submission/listing is clicked
   * @param {Object} submission - The submission object
   * @returns
   */
  _onJobViewClick(submission={}){
    if ( !submission.meta_data ) return;
    const pageId = 'detail';

    const page = this.pages.find(p => p.id === pageId);
    page.data.submission = submission;

    const formData = [];
    for ( const key in submission.meta_data ) {
      const field = this.getFieldObject(key);
      if ( !field.id ) continue;
      formData.push({
        field,
        value: submission.meta_data[key].value,
        id: submission.meta_data[key].id
      });
    }
    if ( formData.length === 0 ) return;

    page.data.formData = formData;
    page.data.updatedFormData = {};

    this._onPageChange(undefined, pageId);
  }

  /**
   * @description Handles event when the form on the job detail page is submitted
   * @param {*} e - The form submit event
   * @returns
   */
  async _onListingSubmit(e){
    e.preventDefault();
    const page = this.getCurrentPage();
    if ( !page ) return;
    if ( !page.data?.formData?.length ) return;
    const entry_id = page.data.submission.entry_id;
    const payload = {
      meta_data: {}
    };
    page.data.formData.forEach(field => {
      if ( page.data.updatedFormData[field.field.id] ) {
        payload.meta_data[field.field.id] = {
          value: field.value,
          id: field.id
        };
      }
    });

    this._showLoading();
    const data = await this.api.post(`${this.apiEndpoints.updateSubmissionMeta}/${entry_id}`, payload);

    if ( data.status === 'error' ) {
      this.page = 'error';
      return;
    }

    this.replaceSubmissionInListings(data.data);

    this.goToLastPage();
    this.showSuccessMessage('Job listing updated');
  }

  /**
   * @description Replaces the specified submission data object in all page data listing arrays
   * @param {Object} submission
   */
  replaceSubmissionInListings(submission){
    this.pages.forEach(page => {
      if ( !page.hasListings ) return;
      const pageIndex = page.data.pagedSubmissions.findIndex(submissions => submissions.find(s => s.entry_id == submission.entry_id));
      if ( pageIndex > -1 ) {
        const submissionIndex = page.data.pagedSubmissions[pageIndex].findIndex(s => s.entry_id == submission.entry_id);
        if ( submissionIndex > -1 ) {
          page.data.pagedSubmissions[pageIndex][submissionIndex] = submission;
        }
      }
    });
    this.requestUpdate();
  }

  /**
   * @description Handles event when a job detail form input is changed
   * @param {Number} i - The index within the formData array
   * @param {*} value
   * @returns
   */
  _onListingInput(i, value){
    const page = this.getCurrentPage();
    if ( !page ) return;
    if ( !page.data?.formData || !Array.isArray(page.data.formData) || !page.data.formData[i]) return;
    page.data.formData[i].value = value;
    page.data.updatedFormData[page.data.formData[i].field.id] = value;
    this.requestUpdate();
  }

  /**
   * @description Change page to the previous page in the page history
   * @returns
   */
  goToLastPage(){
    if ( this.pageHistory.length === 0 ) return;
    const pageId = this.pageHistory[this.pageHistory.length - 1];
    this._onPageChange(undefined, pageId);
  }

  /**
   * @description Clears cache for current page and retrieves new data
   * @returns
   */
  async refreshCurrentPage(){
    const page = this.getCurrentPage();
    if ( !page ) return;
    const pageIndex = this.getCurrentPageIndex();
    this.clearPageCache(this.page);
    await this._onPageChange(pageIndex);
  }

  /**
   * @description Returns the index of the current page in the pages array
   * @returns
   */
  getCurrentPageIndex(){
    return this.pages.findIndex(p => p.id === this.page);
  }

  /**
   * @description Adds the specified page id to the page history
   * @param {String} pageId - The id of the page to add to the history
   */
  addToPageHistory(pageId){
    if ( !pageId ) pageId = this.page;
    if ( pageId === 'loading' || pageId === 'error' ) return;
    this.pageHistory.push(pageId);

    if ( this.pageHistory.length > 10 ) {
      this.pageHistory.shift();
    }
  }

  /**
   * @description Returns the current page object
   * @returns {Object}
   */
  getCurrentPage(){
    return this.pages.find(p => p.id === this.page);
  }

  /**
   * @description Returns the label for the specified listing action
   * @param {String} action - An action from the page.data.actions object
   * @returns {String}
   */
  getListingActionLabel(action){
    if ( action === 'approve' ) return 'Approve';
    if ( action === 'deny' ) return 'Deny';
    if ( action === 'expire' ) return 'Expire';
    if ( action === 'revertToPending' ) return 'Revert to Pending';
    if ( action === 'delete' ) return 'Delete';
    return '';
  }

  /**
   * @description Retrieves the listings for the jobs board by status
   * @param {Object} kwargs - Keyword arguments
   * @param {Number} kwargs.p - Page number
   * @param {String} kwargs.id - Page id. i.e. 'active' or 'expired'
   * @returns
   */
  async getListings(kwargs={}){
    const p = kwargs.p || 1;
    const id = kwargs.id || '';
    const page = this.pages.find(p => p.id === id);
    if ( !page ) return;

    const d = await this.api.get(this.apiEndpoints.submissions + '/' + page.id, {page: p});
    if ( d.status === 'error'  ) return;

    if ( !page.cacheKeys.includes(d.cacheKey) ) {
      page.cacheKeys.push(d.cacheKey);
    }

    // merge paged submissions into array of arrays
    d['data']['pagedSubmissions'] = [...page.data.pagedSubmissions] || [];
    d['data']['pagedSubmissions'][d.data.page - 1] = d.data.submissions;
    d['data']['pagedSubmissions'] = d['data']['pagedSubmissions'].map(submissions => submissions || []);
    return d;
  }

  /**
   * @description Handles event when listing pagination element is changed
   * @param {String} id - Page id. i.e. 'pending'
   * @param {Number} requestedPage - Numerical listing page requested
   * @returns
   */
  async _onListingPaginationChange(id, requestedPage){
    const page = this.pages.find(p => p.id === id);
    this._showLoading();
    const data = await page.getData[0].call(this, {id, p: requestedPage});
    if ( data.status === 'error' ) {
      this.page = 'error';
      return;
    }
    page.data = {...page.data, ...data.data};
    this.page = id;
    this.requestUpdate();
  }

  /**
   * @description Retrieves the admin settings for the jobs board
   * @returns {Object}
   */
  async getSettings(){
    const d = await this.api.get(this.apiEndpoints.settings);
    if ( d.status === 'error' ) return;
    const page = this.pages.find(p => p.id === 'settings');
    if ( !page.cacheKeys.includes(d.cacheKey) ) {
      page.cacheKeys.push(d.cacheKey);
    }
    return d;
  }

  /**
   * @description Handles event fired when action select field for a job board listing is changed
   * @param {String} id - The id of the page
   * @param {Number} submissionId - The id of the submission
   * @param {String} action - 'approve' or 'deny'
   * @returns
   */
  _onListingAction(id, submissionId, action){
    submissionId = parseInt(submissionId);
    if ( !submissionId ) return;

    // update action arrays
    const page = this.pages.find(p => p.id === id);
    Object.keys(page.data.actions).forEach(key => {
      const index = page.data.actions[key].indexOf(submissionId);
      if ( index > -1 ) {
        page.data.actions[key].splice(index, 1);
      }
    });
    if ( action ) {
      page.data.actions[action].push(submissionId);
    }

    this.requestUpdate();
  }

  /**
   * @description Prepares submissions for display on the listing pages
   * @param {Object} page - The page object
   * @returns {Array} - Array of submissions with display properties
   */
  _prepareSubmissionsForDisplay(page){
    const actions = Object.keys(page.data.actions);
    return (page.data.pagedSubmissions[page.data.page - 1] || []).map(submission => {
      submission.display = {action: ''};
      submission.display.jobTitle = submission.meta_data[page.data.assignedFormFields.jobTitle]?.value || '';
      submission.display.employer = submission.meta_data[page.data.assignedFormFields.employer]?.value || '';
      for (const action of actions) {
        if ( page.data.actions[action].includes(submission.entry_id) ) {
          submission.display.action = action;
          break;
        }
      }
      return submission;
    });
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
    const dropProps = ['forms', 'users', 'formFields', 'cronStatus'];
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
    this.api.clearCache();
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
   * @description Handles submit event for all listing pages
   * @param {*} e
   */
  async _onListingActionSubmit(e){
    e.preventDefault();
    const page = this.getCurrentPage();
    const payload = {actions: page.data.actions};

    let hasActions = false;
    for ( let key in payload.actions ) {
      if ( (payload.actions[key]?.length || 0) > 0 ) {
        hasActions = true;
        break;
      }
    }
    if ( !hasActions ) return;
    const d = await this.api.post(this.apiEndpoints.submissions + '/' + page.id, payload);
    if ( d.status === 'error' ) {
      this.page = 'error';
      return;
    }

    page.data.actions = {
      approve: [],
      deny: []
    }
    this.api.clearCache();
    await this.refreshCurrentPage();
    let message = 'Save successful';
    this.showSuccessMessage(message);
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

  /**
   * @description Handles event when a job board form field is matched to a submission form field
   * @param {String} field - The id of the job board form field
   * @param {String} value - The id of the submission form field
   */
  _onSettingsFormFieldSelect(field, value){
    const page = this.pages.find(p => p.id === 'settings');
    page.data.selectedFormFields[field] = value;
    this.requestUpdate();
  }

  /**
   * @description Handles even when the display order of a job board form field is changed
   * @param {*} field - The id of the job board form field
   * @param {*} value - The id of the submission form field
   * @returns
   */
  _onSettingsFormFieldOrderChange(field, value){
    const page = this.pages.find(p => p.id === 'settings');
    value = parseInt(value);
    if ( isNaN(value) ) return;
    page.data.publicFieldDisplayOrder[field] = value;
    this.requestUpdate();
  }

  /**
   * @description Handles event when the display checkbox for a job board form field is toggled
   * @param {*} field - The id of the job board form field
   */
  _onSettingsFormFieldDisplayToggle(field){
    const page = this.pages.find(p => p.id === 'settings');
    const currentValue = page.data.publicFieldDisplayOrder[field] === undefined ? 0 : page.data.publicFieldDisplayOrder[field];
    const newValue = currentValue >= 0 ? -1 : 0;
    page.data.publicFieldDisplayOrder[field] = newValue;
    this.requestUpdate();
  }

  /**
   * @description Returns data structure needed for rendering the field display order settings section
   */
  _fieldDisplayOrderArray(){
    const page = this.pages.find(p => p.id === 'settings');
    const fields = [];
    page.data.formFields.forEach(field => {
      const skipField = Object.values(page.data.selectedFormFields).includes(field.id);
      if ( skipField ) return;
      const f = {
        id: field.id,
        label: field.label,
        order: page.data.publicFieldDisplayOrder[field.id] === undefined ? 0 : page.data.publicFieldDisplayOrder[field.id]
      }
      f.hide = f.order < 0;
      fields.push(f);
    });

    return fields;
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
   * @description Handles event when button for running job status check is clicked
   * @returns
   */
  async _onSettingsEvaluateListingsClick(){
    const page = this.pages.find(p => p.id === 'settings');
    if ( page.data.cronStatus === 'running' ) return;

    page.data.cronStatus = 'running';
    this.requestUpdate();
    const data = await this.api.post(this.apiEndpoints.statusCheck);
    if ( data.status === 'error' ){
      page.data.cronStatus = 'error';
    } else if ( data.data.status === 'running' ) {
      page.data.cronStatus = 'running';
    } else {
      page.data.cronStatus = 'complete';

      setTimeout(() => {
        page.data.cronStatus = '';
        this.requestUpdate();
      }, 10000);

    }
    this.requestUpdate();
    this.api.clearCache();
  }

  /**
   * @description Clears the data cache for the specified page
   * @param {String} pageId - The id of the page to clear the cache for
   */
  clearPageCache(pageId){
    const page = this.pages.find(p => p.id === pageId);
    const cacheKeys = page.cacheKeys || [];
    cacheKeys.forEach(key => {
      this.api.clearCache(key);
    });
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
