import { html, css } from 'lit';

import formStyles from "@ucd-lib/theme-sass/1_base_html/_forms.css.js";
import formsClasses from "@ucd-lib/theme-sass/2_base_class/_forms.css.js";
import l3col from "@ucd-lib/theme-sass/5_layout/_l-3col.css.js";
import lgridRegions from "@ucd-lib/theme-sass/5_layout/_l-grid-regions.css.js";
import spaceUtils from "@ucd-lib/theme-sass/6_utility/_u-space.css.js";

import ElementStatusController from "../../controllers/element-status.js";

export function styles() {
  const elementStyles = css`
    :host {
      display: block;
    }
    [hidden] {
      display: none !important;
    }

    select {
      background-color: #B0D0ED;
      color: #022851;
      font-weight: 700;
      border: none;
      font-size: 1rem;
    }
    select:focus {
      background-color: #b0d0ed;
      border: 1px solid #999;
    }
    .sort-container {
      display: flex;
    }
    .sort-container .section-label {
      font-weight: 700;
    }
    .sort-container label {
      margin-left: .25rem;
    }
    .separator {
      margin: 2rem 0;
      border-bottom: 4px dotted #FFBF00;
    }
    @media (max-width: 330px) {
      .sort-container {
        flex-direction: column;
      }
    }
  `;

  return [
    formStyles,
    formsClasses,
    l3col,
    lgridRegions,
    spaceUtils,
    ElementStatusController.styles,
    elementStyles
  ];
}

/**
 * @description main render function for the element
 */
export function render() {
  if ( !this.SsrPropertiesLoaded ) return html``;
  return html`
    ${ renderSearchForm.call(this) }
    <div class='separator'></div>
    ${ renderResults.call(this) }
`;}

/**
 * @description render the search results or loading/error message
 */
function renderResults(){
  if ( this.status == 'loading' ) return this.statusController.renderLoading();
  if ( this.status == 'loaded' ) return html`
    <div id='loaded'>
      <div ?hidden=${this.projects.length}>No Results</div>
      <div ?hidden=${!this.projects.length}>
        ${this.projects.map(project => html`
          <p>${project.title}</p>
        `)}
      </div>
    </div>
  `;
  return this.statusController.renderError();
}

/**
 * @description render the search/filter form
 */
function renderSearchForm(){
  return html`
    <form @submit=${this._onSubmit}>
      <div class='l-3col'>
        <div class='l-first u-space-mb'>
          ${ renderFilterSelect.call(this, this.themeFilters, 'selectedTheme', 'Any Theme', 'Filter by theme') }
        </div>
        <div class='l-second u-space-mb'>
          ${ renderFilterSelect.call(this, this.approachFilters, 'selectedApproach', 'Any Approach', 'Filter by approach') }
        </div>
        <div class='l-third u-space-mb'>
          ${ renderFilterSelect.call(this, this.statusFilters, 'selectedStatus', 'Any Status', 'Filter by status') }
        </div>
      </div>
      <div class='sort-container'>
        <div class='section-label'>Sort:</div>
        <div class='radio'>
          <input type='radio' id='sort-date' .checked=${!this.orderBy} @change=${(e) => this._onFieldInput('orderBy', '')} />
          <label for='sort-date'>Newest First</label>
        </div>
        <div class='radio'>
          <input type='radio' id='sort-title' .checked=${this.orderBy == 'title'} @change=${(e) => this._onFieldInput('orderBy', 'title')} />
          <label for='sort-title'>A - Z</label>
        </div>
      </div>
    </form>
  `
}

/**
 * @description render taxonomy filter select
 * @param {Array} options array of taxonomy options
 * @param {String} selectedProp element property to bind selected value to
 * @param {String} defaultLabel default label for select
 * @param {String} ariaLabel aria label for select
 */
function renderFilterSelect(options, selectedProp, defaultLabel, ariaLabel){
  return html`
    <select
      role="link"
      aria-label=${ariaLabel}
      @change=${(e) => this._onFieldInput(selectedProp, e.target.value)}>
      <option value="" ?selected=${!this[selectedProp]}>${defaultLabel}</option>
      ${options.map(option => html`
        <option value=${option.slug} ?selected=${this[selectedProp] == option.slug}>${option.name}</option>`
      )}
    </select>
  `;
}
