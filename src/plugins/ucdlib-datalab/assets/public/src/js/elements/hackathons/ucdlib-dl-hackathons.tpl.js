import { html, css } from 'lit';

import ElementStatusController from "../../controllers/element-status.js";
import headings from "@ucd-lib/theme-sass/1_base_html/_headings.css.js";
import formStyles from "@ucd-lib/theme-sass/1_base_html/_forms.css.js";
import formsClasses from "@ucd-lib/theme-sass/2_base_class/_forms.css.js";

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
      padding: 1rem 0;
    }
    .sort-container .section-label {
      font-weight: 700;
    }
    .sort-container label {
      margin-left: 0;
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
    .search-form {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .filters {
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-gap: 1rem;
      width: 100%;
      max-width: 375px;
    }
    @media (max-width: 1100px) {
      .search-form {
        display: block;
      }
    }
    .result {
      margin-bottom: 1rem;
    }
    .result__content h4 a {
      text-decoration: none;
    }
    .result__content h4 a:hover {
      text-decoration: underline;
    }
    .result__type {
      font-style: italic;
      font-size: .875rem;
      color: #4C4C4C;
      margin-bottom: .5rem;
    }
  `;

  return [
    headings,
    formStyles,
    formsClasses,
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

function renderSearchForm(){
  return html`
    <form @submit=${this._onSubmit} class='search-form'>
      <div class='filters'>
        <select
          role="link"
          aria-label='Filter by Year'
          @change=${(e) => this._onFieldInput('selectedYear', e.target.value)}>
          <option value="" ?selected=${!this.selectedYear}>All Years</option>
          ${this.yearFilters.map((year) => html`
            <option value=${year} ?selected=${this.selectedYear === year}>${year}</option>
          `)}
        </select>
        <select
          role="link"
          aria-label='Filter by Type'
          @change=${(e) => this._onFieldInput('selectedType', e.target.value)}>
          <option value="" ?selected=${!this.selectedType}>All Types</option>
          ${this.typeFilters.map((t) => html`
            <option value=${t.slug} ?selected=${this.selectedType === t.slug}>${t.name}</option>
          `)}
        </select>
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

function renderResults(){
  if ( this.status == 'loading' ) return this.statusController.renderLoading();
  if ( this.status == 'loaded' ) return html`
    <div id='loaded'>
      <div ?hidden=${this.results.length}>${this.statusController.renderNoResults()}</div>
      <div ?hidden=${!this.results.length}>
        ${this.results.map((result) => this.orderBy === 'title' ? renderResult.call(this, result) : html`
          <h3>${result.year}</h3>
          ${result.results.map((r) => renderResult.call(this, r))}
        `)}
      </div>
    </div>
  `;

  return this.statusController.renderError(this.errorMessage);
}

function renderResult(result){
  const resultType = (result?.hackathonTypes || []).length ? result.hackathonTypes[0].name : '';
  return html`
    <div class='result'>
      <div class='result__img'></div>
      <div class='result__content'>
        <h4><a href=${result.hackathonLandingPageUrl}>${result.hackathonTitle}</a></h4>
      </div>
      <div class='result__type'>${resultType}</div>
      <div class='result__excerpt'>${result.hackathonExcerpt}</div>
    </div>
  `;
}
