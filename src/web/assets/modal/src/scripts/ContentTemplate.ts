import ContentTemplateSettings from './ContentTemplateSettings'

export default class ContentTemplate {
  /**
   * The content template ID.
   * @public
   */
  public readonly id?: number

  /**
   * The content template title.
   * @public
   */
  public readonly title: string

  /**
   * The content template description.
   * @public
   */
  public readonly description: string

  /**
   * The content template button.
   * @public
   */
  public readonly $button: JQuery<HTMLElement>

  /**
   * The constructor.
   * @param settings - A `ContentTemplateSettings` object.
   * @public
   */
  constructor (settings: ContentTemplateSettings) {
    this.id = settings.id
    this.title = settings.title
    this.description = settings.description
    this.$button = $(this._button())
  }

  /**
   * Gets the button JQuery object to use for this content template in the selection modal.
   * @returns the button
   * @private
   */
  private _button (): JQuery<HTMLElement> {
    const $button = $('<button />')
      .append($('<p />').text(this.title))

    if (this.description !== null) {
      $button.append($('<p />').text(this.description))
    }

    return $button
  }
}
