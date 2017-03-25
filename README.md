# Truffade

Truffade is an API mock server. It helps you to mock the third party API.

## Install

The recommended way to install Truffade is through [Composer](http://getcomposer.org/):

```cli
$ composer require krichprollsch/truffade
```

## Usage

### Start the server

First you need to start the server

```cli
php ./vendor/bin/truffade --admin-port=8081 --mock-port=8080
```

### Configure the mock responses

Now you can configure any mock response you want the server returns using the
admin port.

```cli
$ curl -XPOST http://127.0.0.1:8081 --data '{"body":{"foo":"bar"}}'
{"total":1,"next":0}

$ curl -XPOST http://127.0.0.1:8081 --data '{"body":{"foo":"baz"}}'
{"total":2,"next":0}
```

### Request the mock server

Then you can use the mock server through you application.
The responses will be return into the order of the configuration time.

```cli
$ curl -XGET http://127.0.0.1:8080/foo/bar
{"foo":"bar"}

$ curl -XPOST http://127.0.0.1:8080/what/you/want --data '{"foo"}'
{"foo":"baz"}
```

If there is no more responses configures, an error is returned.

```cli
$ curl -XPOST http://127.0.0.1:8080/yoohoo
{"err":"not configured yet"}
```

### Consult the requests

The admin server allow you to check the requests send by you application.

```cli
curl -XGET http://127.0.0.1:8081/0
{
    "request": {
        "body": null,
        "headers": {
            "Accept": [
                "*/*"
            ],
            "Host": [
                "127.0.0.1:8080"
            ],
            "User-Agent": [
                "curl/7.50.1"
            ]
        },
        "json": null,
        "method": "GET",
        "path": "/foo/bar",
        "query": "",
        "request": {}
    },
    "response": {
        "foo": "bar"
    }
}
```

## Run test

```
// TODO
```

## Truffade?

> Truffade is a rural dish traditionally associated with Auvergne in France.
[see Wikipedia](https://en.wikipedia.org/wiki/Truffade)

## License

Truffade is released under the MIT License. See the bundled LICENSE file for
details.
