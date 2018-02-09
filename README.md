# VPDB for IPS4

This adds VPDB integration to your [IPS Community Suite 4](http://invisionpower.com/).

*Work in progress.*

## Quickstart

Copy this into a `vpdb` folder of your IPS `applications` folder. Enable 
developer mode. Start with `php -S localhost:3003` or wherever your IPS
installation is running.

## Architecture

To tightly integrate with IPS, VPDB's models need to be something IPS can deal 
with. Typically, a release would be a `IPS\vpdb\Release`, extending `IPS\Content\Item`.
Because instances are not persisted locally but fetched through the VPDB API,
there is some special treatment necessary:

- While items are usually instantiated with `IPS\Content::constructFromData()`,
  we simply add a constructor that takes a unmarshalled `stdObject` from the API. 
- While the actual item object is often needed for reference by IPS, it actually
  never cares much about the actual data but more about which interfaces it 
  implements and which kind of IDs are to be retrieved. Thus, when we don't have
  the actual data from VPDB, it's usually enough to instantiate the item with 
  only its ID(s) set.

## Features

### Users

Basically we want to identify VPDB users with IPS users so they can get the
reputation at IPS, but also star, rate and follow stuff at IPS. This is done
using the [OAuth2 Server Application](https://github.com/wohali/ips4-oauth2-server),
which lets IPS users log at VPDB. The other way around is possible as well, i.e.
users who want to access VPDB resources at IPS will get a one-click 
registration within IPS.

This allows VPDB to include IPS user IDs whenever user data is returned so
the IPS app can identify the user and render the widgets accordingly. 

### Comments

IPS users can comment releases like they can downloads, gallery images, or 
whatever else. It's not as rich as forum threads, but it should be sufficient.
You should be able to follow and be notified about mentions. Also, comments can
be liked, work with the reputation system etc etc.

Comments are stored locally at IPS, while the parent object (the release) is
fetched from VPDB. The challenge was to present the remote object to IPS as
a local item. 

While reading through the guides [here](https://invisioncommunity.com/developers/docs/fundamentals/comments/the-comment-model-r108/)
is certainly useful, they leave out all the bits an pieces how to deal with 
the million of edge cases, such as:

- Deleting comments: 
  - Needs a parent item, even if it's just an empty stub
  - Controller needs to extend `IPS\Content\Controller`, not `IPS\Dispatcher\Controller`
- Liking comments:
  - Parent item now needs a numerical ID, but only because of the mod log
- Moderating comments:
  - I'm not even that far
- Reputation
- Global Search
- Notify followers

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

### Tags

IPS has tags, VPDB has tags. No idea if there's a way to merge them.

*TBD*

  
## License

GPLv2. See [LICENSE](LICENSE).