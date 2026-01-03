# FastRaven  
**State:** use only for personal projects  
**Version:** v0.3  

FastRaven is a minimalistic and fast PHP framework for **monolithic apps**.  
It focuses on simplicity, clarity, and raw performance: no unnecessary layers, no dependencies you don’t control.

---

### Requeriments  
- **PHP** ^8.4  
- **MySQL** ^8.0  
- **Composer** ^2.9.2  

---

### Installation  
1. `composer create-project fast-raven/project app` : Creates an example project inside folder app.
2. `cd app` : Move inside fast-raven project folder.
3. `./bin/build.sh` : Install required packages.

OR (not recommended)

1. `composer require fast-raven/library` : Install fast-raven framework inside existing composer project.

---

### Roadmap: v0.5
- Addition of frontend modular components.

### Roadmap: v1.0
- Improvement of ValidationWorker complexity and ValidationFlags.
- Implementation of unauthorized flow with redirects after login.
- ~~Upgrade of Router component: refactor to support more complex architectures.~~
- Upgrade of PHP version to 8.5 (if is globally supported by hosting providers).
- Upgrade of DataSlave: implementation of index and join for scalable queries.
- Upgrade of ValidationWorker: implementation of validation details when validation fails.

---

### Namespaces FastRaven\
- **\Components** : Little modular objects that fast-raven uses to function.
- **\Exceptions** : Catchable exceptions.
- **\Internal** : Get your hands out of here.
- **\Workers** : Static classes that interact with the server.

---

**Author:** arkhyst  
**License:** MIT (see LICENSE)  