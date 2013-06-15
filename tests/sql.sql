CREATE TABLE cache (
	key VARCHAR(255) PRIMARY KEY,
	value TEXT
);
;
CREATE TABLE "tags" (
    "tag" TEXT NOT NULL,
    "key" TEXT NOT NULL
);
CREATE INDEX "tags_tag_index" on tags (tag ASC);
