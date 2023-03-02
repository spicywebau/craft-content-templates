/**
 * An instance of Craft.
 */
declare const Craft: {
  BaseElementIndex: any
  createElementEditor: (elementType: string, element: JQuery|Element|object, settings?: object) => any
  getUrl: (path: string, params?: object|string, baseUrl?: string) => string
  publishableSections: [Section]
  randomString: (length: number) => string
  registerElementIndexClass: (elementType: string, func: Function) => void
  sendActionRequest: (method: string, action?: string, options?: object) => Promise<any>
  setPath: (path: string) => void
  t: (category: string, message: string, params?: object) => string
  ui: any
}

interface Section {
  handle: string
  sites: number[]
  entryTypes: EntryType[]
}

interface EntryType {
  handle: string
  id: number
  name: string
  section?: Section
  uid: string
}

interface BaseElementIndexInterface {
  $source: JQuery|null
  addButton: ($button: JQuery) => void
  clearSearch: () => void
  elementType: string
  selectElementAfterUpdate: (id: number) => void
  selectSourceByKey: (key: string) => void
  setSelectedSortAttribute: (attr: string, dir: string) => void
  settings: {
    context: string
  }
  siteId: number
  sourceKey: string
  updateElements: () => void
}
