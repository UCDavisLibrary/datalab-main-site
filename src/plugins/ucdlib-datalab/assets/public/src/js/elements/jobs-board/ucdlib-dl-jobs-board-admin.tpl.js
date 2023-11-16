import { html, css } from 'lit';

import normalize from "@ucd-lib/theme-sass/normalize.css.js";
import baseHtml from "@ucd-lib/theme-sass/1_base_html/_index.css.js";
import baseClass from "@ucd-lib/theme-sass/2_base_class/_index.css.js";
import oBox from "@ucd-lib/theme-sass/3_objects/_index.css.js";
import brandBox from "@ucd-lib/theme-sass/4_component/_brand-textbox.css.js";
import marketingHighlight from "@ucd-lib/theme-sass/4_component/_marketing-highlight.css.js";
import panel from "@ucd-lib/theme-sass/4_component/_panel.css.js";
import brandColors from "@ucd-lib/theme-sass/4_component/_category-brand.css.js";
import layouts from "@ucd-lib/theme-sass/5_layout/_index.css.js";
import spaceUtils from "@ucd-lib/theme-sass/6_utility/_u-space.css.js";

export function styles() {
  const elementStyles = css`
  :host {
    display: block;
    background-color: #fff;
    color: #000;
    margin-right: 1rem;
    margin-top: 1rem;
    padding-top: 2rem;
    padding-bottom: 2rem;
    line-height: 1.618;
    font-size: 1rem;
    box-sizing: border-box;
    font-family: "proxima-nova", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
  }
  .heading-container {
    display: flex;
    align-items: center;
  }
  .heading-container img {
    margin-right: 1rem;
  }
  @media screen and (max-width: 450px) {
    .heading-container img {
      display: none;
    }
  }
  .loading-icon {
    display: flex;
    justify-content: center;
  }
  .loading-icon ucdlib-icon {
    animation: spin 2s linear infinite, opacity-pulse 2s linear infinite;
    width: calc(3vw);
    height: calc(3vw);
    max-width: 50px;
    max-height: 50px;
    color: #022851;
  }
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(359deg); }
  }
  @keyframes opacity-pulse {
    0% { opacity: 0.4; }
    25% { opacity: 0.5; }
    50% { opacity: 1; }
    75% { opacity: 0.5; }
    100% { opacity: 0.4; }
  }
  .marketing-highlight__cta:hover, .marketing-highlight__cta:focus {
    text-decoration: none;
    color: var(--category-brand-contrast-color, rgb(2, 40, 81));
  }
  .hint-text {
    font-size: 0.875rem;
    color: #13639e;
  }
  .board-manager-list__header {
    font-weight: 700;
    color: #022851;
    padding: 0 .5rem;
  }
  .board-manager-list__checkbox {
    display: flex;
    align-items: center;
  }
  .board-manager-list__checkbox label {
    margin-left: 0.5rem;
    padding-bottom: 0;
  }
  .board-manager-list__row {
    padding: .5rem;
  }
  .board-manager-list__row:nth-child(odd) {
    background-color: #ebf3fa;
  }

  @media screen and (max-width: 993px) {
    .hide-on-mobile {
      display: none !important;
    }
  }
  @media screen and (min-width: 992px) {
    .hide-on-desktop {
      display: none !important;
    }
  }


  `;

  return [
    normalize,
    baseHtml,
    baseClass,
    oBox,
    brandBox,
    marketingHighlight,
    panel,
    brandColors,
    layouts,
    spaceUtils,
    elementStyles
  ];
}

/**
 * @description Main render function for element
 */
export function render() {
return html`
  <div class='l-container'>
    <div class='heading-container'>
      ${this.logoUrl ? html`<img src=${this.logoUrl} width=${this.logoWidth} />` : html``}
      <h2 class='heading--weighted-underline'><span class='heading--weighted--weighted'>Jobs Board</span></h2>
    </div>
    <div ?hidden=${!this.successMessage.show} class="brand-textbox category-brand--farmers-market">
      <p>${this.successMessage.message}</p>
    </div>
    <div class='l-basic--flipped'>
      <div class='l-content'>
        <div class='panel o-box'>
          <ucdlib-pages selected="page-${this.page}">
            ${this.pages.map(page => html`
              <div id="page-${page.id}">
                ${page.render()}
              </div>
            `)}
          </ucdlib-pages>
        </div>
      </div>
      <div class='l-sidebar-second'>
        <div class='panel o-box'>
          <ucd-theme-subnav @item-click=${e => this._onPageChange(e.detail.location[0])}>
            ${this.pages.filter(p => !p.noNav).map(page => html`
              <a>${page.name}</a>
            `)}
          </ucd-theme-subnav>
        </div>
      </div>
    </div>
  </div>
`;}

/**
 * @description Renders the page that displays any pending job submission requests
 */
export function renderPendingRequests(){
  return html`
    <h3>Pending Requests</h3>
    <p>These are the pending requests</p>
  `;
}

/**
 * @description Renders the page that displays any current active job listings
 */
export function renderActiveListings(){
  return html`
    <h3>Active Listings</h3>
    <p>These are the active listings</p>
  `;
}

/**
 * @description Renders the page that displays any expired job listings
 */
export function renderExpiredListings(){
  return html`
    <h3>Expired Listings</h3>
    <p>These are the expired listings</p>
  `;
}

/**
 * @description Renders the page that displays the settings for the jobs board
 */
export function renderSettings(){
  const id = 'settings'
  const page = this.pages.find(p => p.id == id);
  return html`
    <form @submit=${this._onSettingsSubmit}>
      <h3>Settings</h3>
      <fieldset>
        <legend>Submission Form Settings</legend>
        <div class="field-container">
          <label>Form</label>
          <select
            @input=${e => this._onPageDataInput(id, 'selectedForm', e.target.value)}
            .value=${page.data.selectedForm}>
            <option value="" >Select an existing form</option>
            ${page.data.forms.map(form => html`
              <option value=${form.id} ?selected=${page.data.selectedForm == form.id}>${form.title}</option>
            `)}
          </select>
        </div>
      </fieldset>
      <fieldset>
        <legend>User Settings</legend>
        <label>Jobs Board Managers</label>
        <p>These users will be able to approve or reject job submissions.</p>
        <div class='board-manager-list u-space-mb'>
          <div class="l-2col l-2col--67-33 board-manager-list__header hide-on-mobile">
            <div class="l-first">Name</div>
            <div class="l-second">Remove</div>
          </div>
          ${page.data.users.filter(u => u.isSiteAdmin || u.isBoardManager).map(user => html`
          <div class="l-2col l-2col--67-33 board-manager-list__row">
            <div class="l-first">
              <div class="u-space-mb--small">
                <div>${user.name}</div>
                <div ?hidden=${!user.isSiteAdmin} class='hint-text'>Site Administrator</div>
              </div>
            </div>
            <div class="l-second">
              <div class="flex board-manager-list__checkbox">
                <input
                  id="user-remove-${user.id}"
                  type="checkbox"
                  @change=${() => this._onManagerRemoveToggle(user.id)}
                  ?checked=${page.data.removeBoardManagers.includes(user.id)}
                  ?disabled=${user.isSiteAdmin}>
                <label class='hide-on-desktop' for="user-remove-${user.id}">Remove</label>
              </div>
            </div>
          </div>
          `)}
        </div>
        <div class="field-container">
          <label>Add Managers</label>
          <ucd-theme-slim-select @change=${e => this._onPageDataInput(id, 'addBoardManagers', e.detail.map(u => parseInt(u.value)))}>
            <select multiple>
              ${page.data.users.filter(u => !u.isSiteAdmin && !u.isBoardManager).map(user => html`
                <option value=${user.id} ?selected=${page.data.addBoardManagers.includes(user.id)}>${user.name}</option>
              `)}
            </select>
          </ucd-theme-slim-select>
        </div>
      </fieldset>
      <button type="submit" class='btn btn--primary'>Save</button>
    </form>

  `;
}

/**
 * @description Renders a loading icon
 */
export function renderLoading(){
  return html`
    <div class='loading-icon' style="height:${this.loadingHeight};">
      <ucdlib-icon icon="ucd-public:fa-circle-notch"></ucdlib-icon>
    </div>
  `;
}

/**
 * @description Renders an error message
 */
export function renderError(){
  return html`
    <div class="brand-textbox category-brand--double-decker">
      <p>An unexpected error occurred. Please try again later.</p>
    </div>

  `;
}
