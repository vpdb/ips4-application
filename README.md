# VPDB for IPS4

This adds VPDB integration to your [IPS Community Suite 4](http://invisionpower.com/).

*Work in progress.*

## Quickstart

Copy this into a `vpdb` folder of your IPS `applications` folder. Enable 
developer mode. Start with `php -S localhost:3003`, but probably you'll want to
use Nginx.

## Architecture

To tightly integrate with IPS, VPDB's models need to be something IPS can deal 
with. Typically, a release would be a `IPS\vpdb\Release`, extending 
`IPS\Content\Item`. Originally the idea was to fetch all instances always 
through the VPDB API, but while that works somewhat well for just listing stuff,
it becomes quickly a mess when dealing with notifications and reputation.

So we keep a small table with VPDB releases that contain just VPDB's IDs, the
game title and IPS-related data, and we only fetch through the API when we need
more data. However, since the only goal is to have a local reference, we don't
want local release data show up in any kind of results, so a hook is used to
not index local releases.

## Features

### Users

Basically we want to match VPDB users with IPS users so they can get the
reputation at IPS, but also star, rate and follow stuff at IPS. This is done
using the [OAuth2 Server Application](https://github.com/wohali/ips4-oauth2-server),
which lets IPS users log at VPDB. The other way around is possible as well, i.e.
users who want to access VPDB resources at IPS will get a one-click 
registration within IPS.

This allows VPDB to include IPS user IDs whenever user data is returned so
the IPS app can identify the user and render the widgets accordingly.

Feature Set:

- [X] Identify and link IPS users based on VPDB data
- [X] Login at VPDB through IPS
- [ ] Login at IPS through VPDB 


### Activity Streams

At IPS, we obviously want to include VPDB-related data in the user's activity
stream. This can be done through the [StreamItems Extension](https://invisioncommunity.com/developers/docs/development/extensions/corestreamitems-r161/),
which allows to include extra items to stream results.

We do this by not including local releases in IPS' search index but fetching
them directly through the API. 

- [ ] List VPDB data in the user's activity stream
- [ ] Display VPDB stats in user's info popup
- [ ] Get notified when a followed user creates content at VPDB

Note that these extra items are only fetched when IPS knows the last date to
fetch, meaning that a user with no activity won't get any VPDB data displayed,
even if there is some.

### Comments

IPS users can comment releases like they can downloads, gallery images, or 
whatever else. Comments can be reacted to, they are linked to the reputation 
system, show up in searches and activity streams. In short, they behave like 
any other content.

Comments are stored locally at IPS, while the parent object (the release) is
fetched from VPDB. This somewhat works when creating and listing comments, but
it doesn't integrate well with IPS' notification system, which needs the name
of the parent and not right after comment creation when we still have it, but
also later when users log on.

So we create a `vpdb_releases` table that just holds data that usually doesn't
change, like release and game ID (and game name for displaying). Additionally
it holds IPS' internal counters for comments.

The [comments guide](https://invisioncommunity.com/developers/docs/fundamentals/comments/the-comment-model-r108/)
gives a rough overview of the comment class that needed to be implemented. Also,
in order for the comment to show up in the user's activity stream, a 
[ContentRouter Extension](https://invisioncommunity.com/developers/docs/development/extensions/the-corecontentrouter-extension-r101/)
must be created.

This is the current feature set of release comments:

- [X] Write
- [X] Delete
- [X] Add reaction to comment
- [X] Comment shows up in user's activity stream
- [ ] Reaction shows up in both users' activity streams
- [ ] Comment shows up in global search
- [ ] Get notified about new comments
- [ ] Moderation (e.g. blocked users can't post)

### Reputation

Releases and other VPDB content can be reacted to, using [IPS 4.2's new
reaction API](https://invisioncommunity.com/news/product-updates/new-reactions-r1016/).
The problem is that VPDB can have multiple authors per release, while IPS 
supports only one single content creator per item.

At first, we tried to insert multiple reactions (for each author), but that 
resulted in copying nearly all of the reaction code and other issues, specially
when searching.

Finally the following approach was chosen:

- There is still only one reaction created. Which is generally what we want, 
  because the reaction is really about the person who reacted and the content,
  which stays the same for multiple authors.
- However, reputation is added to all authors, and also all authors are 
  notified about the reaction.
  
The reputation is computed incrementally, meaning it's not computed from the
search index (which would only contain one author). However, the admin can 
choose to re-compute it manually. In this case, there is a hook that ignores
the index for VPDB data (since the author is random) and retrieves the
number of releases directly from the VPDB API in order to correctly compute the 
reputation.

This will also be handy for new users, e.g. if an author who has releases
with reactions on VPDB registers at IPS, its reputation can be easily updated.  

### Downloads

Downloads are handled the same as on the VPDB website: A `GET` request with a 
temp token to the storage backend, resulting in the content being streamed into
a .zip file.

However, IPS can restrict, limit or disable downloads individually for users.
Currently access rights are entirely handled at VPDB (i.e. everyone has access),
but there might be a way to incorporate minimal profile exchange such as blocked
user status or spammer prevention.

### Global Search

Obviously, VPDB's content should show up in IPS' global search. This is probably
pretty easy using one of the IPS extensions. Also, the user profiles should 
show content created at VPDB.

*TODO*

## License

GPLv2. See [LICENSE](LICENSE).