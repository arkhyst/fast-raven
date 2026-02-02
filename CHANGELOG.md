## [v1.1] - 2026-03-??
### [v1.0.0] - 2026-02-01
#### Architecture
- **MAJOR CHANGE**: Workers are now called Services
- **MAJOR CHANGE**: Slaves are now called Engines
- **MAJOR CHANGE**: Namespaces verbosity improved.
- Removed DotEnv library and replaced with native php environment files.
- Framework DOM scripts are now compiled through ops/compile.sh and loaded as a legit script in public.

#### New features
- Template::setErrorPage() for linking an error page to a specific error code.
- Configurable shmop max size via SHMOP_MAX_SIZE environment variable.
- Support for external scripts and styles in Template::addScript() and Template::addStyle().
- Support for external favicons in Template::setFavicon() and Template::setFaviconLight() and Template::setFaviconDark().
- Bee::defineEnv() for defining environment variables.
- Template::getNonce() for including inline scripts inside views.
- Skeleton: added init.sh script for quick project setup.

#### Changes
- Template::addData() now accepts string parameters instead of Pair objects.
- Bee::getCacheKey() now includes the project version in the cache key to avoid cache conflicts.
- Language data is now cached in memory for 24 hours.
- Language data cache is now cleared on dev mode.
- Language JS object is renamed to LANG_INTERNAL.
- Language current language is now stored in localStorage and retrieved on page load.
- Shmop cache backend now uses dynamic segment sizing according to data size.
- Skeleton: Operation script watch.sh is now called compile.sh.
- Skeleton: Operation scripts improved significantly.
- Bee namespace changed from FastRaven\Workers to FastRaven.
- Improved and upgraded CSP policy and base template rendering.
- Environment variables are now loaded from config/env/env.php file.

#### Fixes
- *PHP throws a warning when headers are sent before session_start().*
- *Page redirection on view error do not work.*
- *When returning baseTemplate as a View response, it duplicates styles, scripts and fragments.*
- *Request time in logs is not an accurate measurement of actual request processing time.*
- *Any Database query without conditions throws a DeveloperException.*

#### Removed features
- Stashes and by extension LogStash does not exist anymore.
- .env, .env.dev and .env.prod files have been removed from config/env/ directory.