#Cache Header Listener
This listener sets the cachemeta in the response header.
Several caches can be configured via YML

(https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching#cache-control)
## Configuration
```yaml
    # app/config/config*.yml
    
    ibrows_rest:
        caches:
            - name: cache1
              type: private
              max_age: 600
```

The type of a cache is either private or public. `no-cache` and `no-store` are currently not supported

These caches can later be accessed via its name. The name have to be configured in the `View` annotation on the action

## Controller
```php
    use Ibrows\RestBundle\Annotation as IbrowsAPI;

     /**
     * @IbrowsAPI\View(
     *     serializerGroups={ "car_detail" },
     *     cachePolicyName="cache1"
     * )
     */
```