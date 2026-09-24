Subject: Re: [Pro Like Button / prolike-button] Security review — SVN commit now live (r3712039)

Hi,

Quick follow-up to my previous reply — the SVN commit is now actually live (my earlier message mentioned it as pending; I hit an authentication issue on my end that's now resolved).

- Trunk: revision 3712039 — https://plugins.trac.wordpress.org/changeset/3712039/prolike-button
- Tag: tags/2.0 — https://plugins.trac.wordpress.org/browser/prolike-button/tags/2.0
- readme.txt Stable tag is set to 2.0, matching the tag

Everything from my previous email still applies (postid validated via absint() + $wpdb->prepare() with %d; all other $wpdb queries also hardened with prepare()/%i since then; Plugin Check issues resolved; text domain unified to prolike-button).

Let me know if you need anything else, or once the listing can be restored.

Thank you,
Andriy Prots (wordpress.org: protport4991)
