plugin {
    tx_searchcore {
        settings {
            connections {
                elasticsearch {
                    host = localhost
                    port = 9200
                }
            }

            indexing {
                tt_content {
                    indexer = Codappix\SearchCore\Domain\Index\TcaIndexer

                    additionalWhereClause (
                        tt_content.CType NOT IN ('gridelements_pi1', 'list', 'div', 'menu', 'shortcut', 'search', 'login')
                        AND tt_content.bodytext != ''
                    )

                    dataProcessing {
                        10 = Codappix\SearchCore\DataProcessing\RelationResolverProcessor
                        10 {
                            tableName = tt_content
                        }
                    }

                    mapping {
                        CType {
                            type = keyword
                        }
                    }
                }

                pages {
                    indexer = Codappix\SearchCore\Domain\Index\TcaIndexer\PagesIndexer
                    abstractFields = abstract, description, bodytext

                    dataProcessing {
                        10 = Codappix\SearchCore\DataProcessing\RelationResolverProcessor
                        10 {
                            tableName = pages
                        }
                    }

                    mapping {
                        CType {
                            type = keyword
                        }
                    }
                }
            }

            searching {
                facets {
                    contentTypes {
                        field = CType
                    }
                }
            }
        }
    }
}

module.tx_searchcore < plugin.tx_searchcore
