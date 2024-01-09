import { LitElement } from 'lit';
import {render, styles} from "./ucdlib-dl-jobs-board.tpl.js";

import WpRest from '../../controllers/wp-rest.js';

export default class UcdlibDlJobsBoard extends LitElement {

  static get properties() {
    return {
      restNamespace: { type: String, attribute: 'rest-namespace' }
    }
  }

  static get styles() {
    return styles();
  }

  constructor() {
    super();
    this.render = render.bind(this);

    this.restNamespace = 'ucdlib-datalab/jobs-board';

    // controllers
    this.api = new WpRest(this);
  }

  async connectedCallback() {
    super.connectedCallback();
    this.jobs = await this.getJobs();
  }

  async getJobs(args){
    return await this.api.get('jobs', args);
  }

}

customElements.define('ucdlib-dl-jobs-board', UcdlibDlJobsBoard);
