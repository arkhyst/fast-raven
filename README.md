# FastRaven  
**State:** use only for personal projects  
**Version:** v0.1-alpha  

FastRaven is a minimalistic and fast PHP framework for **monolithic apps**.  
It focuses on simplicity, clarity, and raw performance: no unnecessary layers, no dependencies you don’t control.

---

### Requeriments  
- **PHP** ^8.5  
- **Composer** ^2.9.2

---

### Installation  
1. `composer create-project fast-raven/project app` : Creates an example project inside folder app.
2. `cd app` : Move inside fast-raven project folder.
3. `./bin/build.sh` : Install required packages.

OR (not recommended)

1. `composer require fast-raven/library` : Install fast-raven framework inside existing composer project.

---

### Roadmap: v0.2
- ~~Modification of Server component, adherence to architecture principles.~~
- ~~Improvement and standardization of exception handling.~~
- ~~Improvement of URI parsing.~~
- ~~Improvement of chache handling.~~
- ~~Improvement of dev and prod domain configuration.~~
- ~~Implementation of typed shared authorization between diferent sites.~~
- ~~Implementation of API security headers for allowed hosts.~~
- ~~Upgrade of AuthWorker: new methods, automatic encryption.~~
- ~~Upgrade of DataWorker: new methods and SQL uses.~~
- ~~Upgrade of Template component: increase template capabilities and flexibility on creation.~~
- ~~Addition of automation script for Apache deployment.~~
- ~~Standarization of JS native libraries for increased compatibility.~~

### Roadmap: v0.3
- Improvement of typed arguments in DataWorker conditions and values.
- Addition of ValidationWorker.

### Roadmap: v1.0
- Upgrade of Router component: refactor to support more complex architectures.
- Implementation of unauthorized flow with redirects after login.

---

### Namespaces FastRaven\
- **\Components** : Little modular objects that fast-raven uses to function.
- **\Exceptions** : Catchable exceptions.
- **\Internal** : Get your hands out of here.
- **\Workers** : Static classes that interact with the server.

---

**Author:** r3dg0bl1n  
**License:** MIT (see LICENSE)  