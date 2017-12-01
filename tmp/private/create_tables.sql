# Todo: NF

CREATE OR REPLACE TABLE members
(
    id SMALLINT UNSIGNED AUTO_INCREMENT KEY,
    username CHAR(255) CHARACTER SET ascii COLLATE ascii_general_ci
)
ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_general_ci;

CREATE OR REPLACE TABLE u2f_authenticators
(
    id SMALLINT UNSIGNED AUTO_INCREMENT KEY,
    member_id SMALLINT UNSIGNED REFERENCES members (id) ON UPDATE CASCADE ON DELETE CASCADE,
    counter SMALLINT UNSIGNED NOT NULL,
    attestation VARCHAR(788) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,-- @todo variable length, @todo in bytes?
    public_key VARCHAR(88) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,-- @todo should be 32 bytes, @todo in bytes
    key_handle VARCHAR(88) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL -- @todo should be 255, @todo in bytes?
)
ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_general_ci;