Il y a deux connections à la bdd :
    recolnat et diff
    recolnat accède à la base centrale de recolnat
    diff accède uniquement à la base temporaire des institutions nommée recolnat_diff
    les deux base ont la même structure
    recolnat à des droits de sélection sur la base recolnat_diff

grant SELECT on "RECOLNAT_DIFF"."BIBLIOGRAPHIES" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."COLLECTIONS" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."DETERMINATIONS" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."DOWNLOAD_LOG" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."INSTITUTIONS" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."LOCALISATIONS" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."MULTIMEDIA" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."MULTIMEDIA_HAS_OCCURRENCES" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."NUM_PICTURAE" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."RECOLTES" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."RESOURCE_MANAGEMENT" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."SPECIMENS" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."STRATIGRAPHIES" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."TAXONS" to "RECOLNAT" ;
grant SELECT on "RECOLNAT_DIFF"."UUIDS_FOR_EXPORT" to "RECOLNAT" ;