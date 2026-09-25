Subject: Re: [Pro Like Button / prolike-button] Security review — SQL injection fix, requesting re-review

Hello,

Thank you for flagging this and for the detailed report. I've fixed the vulnerability and would like to request a re-review so the plugin listing can be restored.

**What was fixed**
The unauthenticated SQL injection in the like/dislike AJAX handler (`wp_ajax_nopriv_id`) has been resolved. The `postid` parameter is now validated with `absint()` and passed through `$wpdb->prepare()` with a `%d` placeholder in every query that uses it, instead of being concatenated directly into SQL.

**What else was done beyond the minimum fix**
- Ran the official Plugin Check plugin against the codebase and resolved the reported issues (remaining `$wpdb` queries using table/column name interpolation are documented with `phpcs:ignore` comments, since those come from internal constants, not user input).
- Fixed several PHP 8+ warnings (activation hook, settings save form, `posts_clauses` filter) that Plugin Check and PHP 8+ compatibility checks would otherwise flag.
- Fixed an "Array to string conversion" notice in the shortcode handler.
- Unified the text domain to `prolike-button` across the codebase (previously inconsistent between `prolike` and `prolikebutton`).
- Bumped the version to 2.0 and added a full changelog entry (the plugin also gained a stats dashboard, rate-limiting, reCAPTCHA support, structured data output, and WooCommerce support since the version you last reviewed — noted in the changelog for transparency, though the security fix itself is isolated and unrelated to these).
- Credited the reporters (Enrico Marcolini, Claudio Marchesini, Dottor Marc) in the readme.txt changelog.

**Where to see the fix**
- Full commit: https://github.com/ProtSport/Pro-Like-Button/commit/209a012
- SVN: I have the 2.0 changes staged in trunk and ready to commit as soon as commit access is restored — my SVN commit is currently rejected by a server-side hook, which I assume is expected while the plugin is closed pending this review.

Please let me know if you need anything else from me, or once commit access / the listing can be restored.

Thank you,
Andriy Prots (wordpress.org: protsport4991)
