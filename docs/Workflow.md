## Workflow(s)

Before execute any action you need to create your table. Table must follow
this rules:

- must have created, modified and deleted fields (datetime)
- must have always an id field (integer unsigned not null)
- cannot have fields with these names (reserved for workflow):
  - notes
  - state
  - is_private
  - is_current
  - transaction

To create a new workflow you can use the cli:

```sh
bin/cake workflow create <EntityName> \
  -s <List of states separated by comma> \
  -t <List of routes in from:to state format separated by comma>
```

The command will do many things (that can be executed separately):

- Create entity model, table and filter (`bin/cake entity create <EntityName>`)
- Create entity transactions _database table_ (`bin/cake workflow create_transaction_table <EntityName>`)
- Create entity transactions _cake model, table and filter_ (`bin/cake entity create <EntityName>Transactions`)
- Create workflow files (`bin/cake workflow create_files <EntityName> -s <States> -t <Transitions>`)
- Create entity API controller (`bin/cake api create <EntityName>`)

**Remember**: when creating workflows, the Core will automatically load the configured entity
as resources following the standard path: `api/<EntityName>` using `dasherized` version of the resource
(suppose you've created a workflow for an entity called `ResearchProjects`, you will
access the resource using `api/research-projects`).
