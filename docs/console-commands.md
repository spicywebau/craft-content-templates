# Console Commands

Content Templates offers console commands for managing the plugin's project config data, and for cleaning up the database based on the contents of the plugin's project config data.

## `content-templates/project-config/cleanup-db`

This command deletes content templates that are not in the project config, if the [`useProjectConfig`][1] plugin setting is set to `true`. If `useProjectConfig` is set to `false`, this command has no effect.

After changing `useProjectConfig` from `false` to `true` and [rebuilding the plugin's project config](#content-templatesproject-configrebuild), it is required to run this in other environments; Craft's `project-config/apply` command won't delete these content templates, since the project config was never aware of them.

This command can also be run with `content-templates/pc/cleanup-db`.

## `content-templates/project-config/rebuild`

This is intended to offer a way to rebuild only the Content Templates project config data, after changing the [`useProjectConfig`][1] plugin setting, rather than rebuilding the entire project config.

Following a rebuild, if `useProjectConfig` has been changed from `false` to `true`, other environments will need to have [`content-templates/project-config/cleanup-db`](#content-templatesproject-configcleanup-db) run.

This command can also be run with `content-templates/pc/rebuild`.

[1]: plugin-settings.md#useprojectconfig
