# Smart Goblin  
**State:** use only for personal projects  
**Version:** v0.1-alpha  

Smart Goblin is a minimalistic and fast PHP framework for **monolithic apps**.  
It focuses on simplicity, clarity, and raw performance: no unnecessary layers, no dependencies you don’t control.

---

### Requeriments  
- **PHP** ^8.5  
- **Composer** ^2.9.2

---

### Installation  
1. `composer create-project smart-goblin/project app` : Creates an example project inside folder app.
2. `cd app` : Move inside smart-goblin project folder.
3. `./bin/build.sh` : Install required packages.

OR (not recommended)

1. `composer require smartgoblin/library` : Install smart-goblin framework inside existing composer project.

---

### Roadmap: v0.2-alpha
- Improvement and standardization of exception handling.
- Improvement of URI parsing.
- Improvement of chache handling.
- Implementation of typed shared authorization between diferent sites.
- Implementation of API security headers for allowed hosts.
- Upgrade of AuthWorker: new methods, automatic encryption.
- Upgrade of DataWorker: new methods and SQL uses.
- Automation script for Apache deployment.

---

### Namespaces SmartGoblin\
- **\Components** : Little modular objects that smart-goblin uses to function.
- **\Exceptions** : Catchable exceptions.
- **\Internal** : Get your hands out of here.
- **\Workers** : Static classes that interact with the server.

---

**Author:** r3dg0bl1n  
**License:** MIT (see LICENSE)  