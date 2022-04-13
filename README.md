# Matrix Hosted Blog
**THIS IS NOT PRODUCTION READY YET**
## What is it?

This is a blog whose entire backend is hosted on the Matrix protocol. The frontend is a simple PHP script to fetch blog "posts" from Matrix.

## How does it work?

The blog itself is a *space* on a Matrix homeserver. I am using Synapse, but any server will work. Each room within the *space* is a *post*. The name of the room is the title of the *post*. The room topic is the tagline for the *post*.

Posts can be published by making the room public, and can be unpublished by making the room private.

The blog is styled using TailwindCSS CDN.

## TODO

- [] It looks fucking horrible.
- [] You can't actually view the body of the posts right now (though the script does support viewing them right now).
- [] Comments in the room could be comments on the post?
- [] Decide if it would make sense to have a web GUI to create a new post.
- [] Structure the project in a way that makes more sense.
- [] Create a composer package so it can be easily integrated into other projects. 


