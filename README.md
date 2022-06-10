# Slack Unfurl App

Example implementation of [spatricius/slack-unfurl-bundle](https://github.com/spatricius/slack-unfurl-bundle)

# Usage

### Set env variables
```env
SLACK_APP_TOKEN=
SLACK_APP_ID=
SLACK_REQUEST_TOKEN=
SLACK_ACCESSORY_IMAGE_URL=
SLACK_ACCESSORY_ALT_TEXT=

GITLAB_TOKEN=
GITLAB_API_URL=
GITLAB_DOMAIN=
```

### Run 
``` 
docker-compose up 
```

or run messenger manually
``` 
php bin/console messenger:consume -vvv 
```
