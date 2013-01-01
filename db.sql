--
-- Structure de la table site
--

CREATE TABLE site (
  id_site SERIAL NOT NULL ,
  site_name varchar(30) NOT NULL,
  site_desc text,
  date_creation TIMESTAMP WITH TIME ZONE NOT NULL,
  date_update TIMESTAMP WITH TIME ZONE,
  PRIMARY KEY (id_site)
);

--
-- Contenu de la table site
--

INSERT INTO site (id_site, site_name, site_desc, date_creation, date_update) VALUES
(1, 'site', NULL, CURRENT_DATE, CURRENT_DATE);

-- --------------------------------------------------------

--
-- Structure de la table theme
--

CREATE TABLE theme (
  id_theme SERIAL NOT NULL ,
  theme_name varchar(50) NOT NULL,
  theme_description text NOT NULL,
  id_site int NOT NULL,
  theme_current boolean NOT NULL DEFAULT FALSE,
  date_creation TIMESTAMP WITH TIME ZONE NOT NULL,
  date_update TIMESTAMP WITH TIME ZONE,
  PRIMARY KEY (id_theme)
);

--
-- Contenu de la table theme
--

INSERT INTO theme (id_theme, theme_name, theme_description, id_site, theme_current, date_creation, date_update) VALUES
(1, 'default', 'theme par default du site', 1, true, CURRENT_DATE, CURRENT_DATE);

