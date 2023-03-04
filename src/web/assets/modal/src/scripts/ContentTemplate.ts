import ContentTemplateSettings from './ContentTemplateSettings'

export default class ContentTemplate {
  /**
   * The content template ID.
   * @public
   */
  public readonly id: number

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
   * The constructor.
   * @param settings - A `ContentTemplateSettings` object.
   * @public
   */
  constructor (settings: ContentTemplateSettings) {
    this.id = settings.id
    this.title = settings.title
    this.description = settings.description
  }

  /**
   * Gets the button HTML to use for this content template in the selection modal.
   * @returns the button HTML as a string
   * @public
   */
  public getButtonHtml (): string {
    // TODO
    return `${this.id} / ${this.title} / ${this.description}`
  }
}
