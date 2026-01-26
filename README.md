# FastRaven  
**State:** use only for non-critical projects
**Version:** v0.5  

FastRaven is a minimalistic and fast PHP framework for **monolithic apps**.  
It focuses on simplicity, clarity, and raw performance: no unnecessary layers, no dependencies you don’t control.

---

### Requeriments  
- **PHP** ^8.4  
- **MySQL** ^8.0  
- **Composer** ^2.9.2  
- **Apache** ^2.4 (if using Apache)  

> **Note:** Remember that web server user should have full permissions to the project folder.
> **Note:** You need to install PHP modules for MySQL, Apache, and other optional extensions.

---

### Installation  
1. `composer create-project fast-raven/project app` : Creates an example project inside folder app.
2. `cd app` : Move inside fast-raven project folder.
3. `./bin/build.sh` : Install required packages.

OR (not recommended)

1. `composer require fast-raven/library` : Install fast-raven framework inside existing composer project.

---

### Potential features for v1.0
- Implementation of Multi-DB support for DataSlave.
- Upgrade of DataSlave: addition of ORM-like component to create complex queries. Refactor of whole class.

---

**Author:** arkhyst  
**License:** MIT (see LICENSE)  
