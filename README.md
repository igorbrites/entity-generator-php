# Entity Generator for PHP

### Generates entities based on database

To use it, install via composer:

```
composer global require igorbrites/entity-generator=dev-master
```

Edit the `config.json` with the options below:
- `namespace`: The namespace of the entities. Default: `null` (e.g.: `My\\Awesome\\Namespace`);
- `output-dir`: (Required!) The output folder (e.g.: `/home/ubuntu/entities`);
- `date-type`: The date fields type. Default `\DateTime` (e.g.: `\\Carbon\\Carbon`);
- `extends`: The class that the entities extends. Default `null` (e.g.: `\\My\\Awesome\\Class`);
- `fk-pattern`: The pattern that fit your FKs. Default `([a-z_]+)_id` (e.g.: `id([a-z_]+)`);
- `database`: (Required!) The database connection parameters:
    - `schema`: The database name. Default `database`;
    - `host`: The database host. Default `127.0.0.1`;
    - `user`: The database user. Default `root`;
    - `password`: The database password. Default ``;

Then, run the command:

```
entity-generator
```

It will generate two folders on the specified output folder, `classes` and `tests`, with your entities.