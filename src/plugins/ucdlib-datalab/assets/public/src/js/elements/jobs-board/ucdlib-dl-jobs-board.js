import { LitElement } from 'lit';
import {render, styles} from "./ucdlib-dl-jobs-board.tpl.js";

import WpRest from '../../controllers/wp-rest.js';
import ElementStatusController from "../../controllers/element-status.js";
import { WaitController } from '@ucd-lib/theme-elements/utils/controllers/index.js';

/**
 * @description Element that displays list of approved jobs to the public
 */
export default class UcdlibDlJobsBoard extends LitElement {

  static get properties() {
    return {
      searchText: { type: String, attribute: 'search-text' },
      filterSector: { type: String, attribute: 'filter-sector' },
      filterEducation: { type: String, attribute: 'filter-education' },
      currentPage: { type: Number, attribute: 'current-page' },
      restNamespace: { type: String, attribute: 'rest-namespace' },
      jobs: { state: true },
      sectors: { state: true },
      educationLevels: { state: true },
      totalPages: { state: true },
      fetchStatus: { state: true },
      expandedJobs: { state: true }
    }
  }

  static get styles() {
    return styles();
  }

  constructor() {
    super();
    this.render = render.bind(this);

    this.restNamespace = 'ucdlib-datalab/jobs-board';
    this.searchText = '';
    this.filterSector = '';
    this.filterEducation = '';
    this.currentPage = 1;
    this.totalPages = 1;
    this.jobs = [];
    this.sectors = [];
    this.educationLevels = [];
    this.fetchStatus = 'loading';
    this.expandedJobs = [];

    // controllers
    this.api = new WpRest(this);
    this.wait = new WaitController(this);
    this.statusController = new ElementStatusController(this, {loadingHeight: '200px'});
  }

  async connectedCallback() {
    super.connectedCallback();
    await this.getJobs();
  }

  /**
   * @description Get active jobs from api for current page and search text
   */
  async getJobs(){
    this.fetchStatus = 'loading';

    // get data from api
    const args = {
      page: this.currentPage
    };
    if (this.searchText) {
      args.search = this.searchText;
    }
    if ( this.filterSector ) {
      args.sector = this.filterSector;
    }
    if ( this.filterEducation ) {
      args.education = this.filterEducation;
    }
    const response = await this.api.get('jobs', args);
    if ( response.status !== 'success' ) {
      console.error(response.error);
      this.fetchStatus = 'error';
      return;
    }

    // extract data
    this.totalPages = response.data.totalPageCt || 1;
    this.jobs = this._transformJobs(response);
    this.sectors = response.data?.filters?.sector || []
    this.educationLevels = response.data?.filters?.education || [];

    this.fetchStatus = 'loaded';
    await this.setLoadingHeight();
  }

  /**
   * @description Handle search submit
   */
  async _onSearchSubmit(e){
    if ( e ) {
      e.preventDefault();
    }
    this.expandedJobs = [];
    this.currentPage = 1;
    await this.getJobs();
  }

  _onInputChange(prop, value, doSearch){
    this[prop] = value;
    if ( doSearch ) {
      this._onSearchSubmit();
    }
  }

  /**
   * @description Set the height of the loading container to the height of the loaded content
   */
  async setLoadingHeight(){
    await this.wait.waitForUpdate();
    await this.wait.waitForFrames(3);
    const container = this.renderRoot.querySelector('#loaded');
    if ( !container ) return;
    const height = container.offsetHeight;
    this.statusController.setLoadingHeight(`${height}px`);
  }

  /**
   * @description Handle pagination change
   */
  _onPageChange(e){
    if ( this.fetchStatus !== 'loaded' ) return;
    if ( e.detail.page == this.currentPage ) return;
    this.currentPage = e.detail.page;
    this.getJobs();
  }

  /**
   * @description Handle click by user - controls visibility for job details
   * @param {Number} id - id of job
   */
  _onJobDetailsToggle(id){
    if ( this.expandedJobs.includes(id) ) {
      this.expandedJobs = this.expandedJobs.filter(j => j !== id);
    } else {
      this.expandedJobs.push(id);
    }
    this.requestUpdate();
  }

  /**
   * @description Transform the response from the api into the format needed for this component
   * @param {*} response
   */
  _transformJobs(response){
    const assignedFormFields = response.data.assignedFormFields;
    const requiredFormFields = ['jobTitle', 'listingEndDate', 'employer'];
    for (const field of requiredFormFields) {
      if ( !assignedFormFields[field] ) {
        console.error(`Missing required field '${field}' mapping. Please check the plugin settings.`);
        return [];
      }
    }
    const adSkipFields = Object.values(assignedFormFields);
    const formFields = response.data.formFields;
    const fieldOrder = response.data.fieldOrder;

    const output = [];
    response.data.jobs.forEach(job => {
      const j = {
        id: job.entry_id,
        title: job.meta_data?.[assignedFormFields.jobTitle]?.value || '',
        employer: job.meta_data?.[assignedFormFields.employer]?.value || '',
        endDate: job.meta_data?.[assignedFormFields.listingEndDate]?.value || '',
        location: job.meta_data?.[assignedFormFields.location]?.value || '',
        listingUrl: job.meta_data?.[assignedFormFields.listingUrl]?.value || '',
        positionType: job.meta_data?.[assignedFormFields.positionType]?.value || '',
        sector: job.meta_data?.[assignedFormFields.sector]?.value || '',
        education: job.meta_data?.[assignedFormFields.education]?.value || '',
        posted: job.meta_data?.['forminator_addon_dl-jb_posted-date']?.value || '',
        additionalFields: []
      }

      for (const fid in job.meta_data) {
        if ( adSkipFields.includes(fid) ) continue;

        let field = formFields.find(f => f.id == fid);
        if ( !field ) continue;
        field = {...field};
        field.value = job.meta_data[fid].value || '';

        const order = fieldOrder[fid];
        if ( order !== undefined && order < 0 ) continue;
        field.order = order || 0;

        j.additionalFields.push(field);
        j.additionalFields.sort((a,b) => {
          if ( a.order < b.order ) return -1;
          if ( a.order > b.order ) return 1;
          return 0;
        });
      }

      output.push(j);
    });

    return output;
  }

}

customElements.define('ucdlib-dl-jobs-board', UcdlibDlJobsBoard);
