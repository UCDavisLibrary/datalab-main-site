import { LitElement } from 'lit';
import {render, styles} from "./ucdlib-dl-jobs-board.tpl.js";

import WpRest from '../../controllers/wp-rest.js';

export default class UcdlibDlJobsBoard extends LitElement {

  static get properties() {
    return {
      searchText: { type: String, attribute: 'search-text' },
      currentPage: { type: Number, attribute: 'current-page' },
      restNamespace: { type: String, attribute: 'rest-namespace' },
      totalPages: { state: true },
      fetchStatus: { state: true },
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
    this.currentPage = 1;
    this.totalPages = 1;
    this.fetchStatus = 'loading';

    // controllers
    this.api = new WpRest(this);
  }

  async connectedCallback() {
    super.connectedCallback();
    await this.getJobs();
  }

  async getJobs(){
    this.fetchStatus = 'loading';

    // get data from api
    const args = {
      page: this.currentPage
    };
    if (this.searchText) {
      args.search = this.searchText;
    }
    const response = await this.api.get('jobs', args);
    if ( response.status !== 'success' ) {
      console.error(response.error);
      this.fetchStatus = 'error';
      return;
    }

    // extract data
    this.totalPages = response.data.totalPages;
    const assignedFormFields = response.data.assignedFormFields;
    const fieldOrder = response.data.fieldOrder;



    this.fetchStatus = 'loaded';
  }

}

customElements.define('ucdlib-dl-jobs-board', UcdlibDlJobsBoard);
