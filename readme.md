### set new user active - link within email to acivate account only for[toc/vendor]
`/index.php?page=api_public&action=set_new_user&type=(vendor|toc)&userDetails=<jsonEncodedObject>`

### set new user active - link within email to acivate account only for[toc/vendor]
`/index.php?page=api_public&action=set_activate_user_self&id=xxx&checksum=xxxxxxxxxxxxxxxxxxxxxxxxxx`

### update user - link within email to acivate account only for[toc/vendor]
`/index.php?page=api_public&action=set_update_user&userDetails=<jsonEncodedObject>`

### get user only for[toc/vendor]
`/index.php?page=api&action=get_user&id=xxx`

### get user only for[toc/vendor]
`/index.php?page=api&action=get_password_check&id=xxx&username=xxxx&password=xxxx`


### get all users with search
`/index.php?page=api&action=get_users[&search=<json_encoded key_value_pairs]`



## Atractions

### get ALL attractions
`/index.php?page=api&action=get_attractions[&search=<json_encoded key_value_pairs>]`

### get attraction by id
`/index.php?page=api&action=get_attraction&id=xxx`

### set pending attraction
`/index.php?page=api&action=set_pending_attraction&attraction=<jsonEncodedObject>`

### set attraction approved
`/index.php?page=api&action=set_attraction_approved&attraction_id=xxx`

### set attraction rejected
`/index.php?page=api&action=set_attraction_approved&offer_id=xxx`

### set attraction deleted
`/index.php?page=api&action=set_attraction_deleted&attraction_id=xxx`

### set bulk update attractions 
`/index.php?page=api&action=set_bulk_update_attractions&offer_ids=<json_encoded_array of attraction ids>[&substitutions=<json_encoded_object of key value pairs>]`



## Offers

### set pending offer
`/index.php?page=api&action=set_pending_offer&offer=<jsonEncodedObject>`

### get ALL offers
`/index.php?page=api&action=get_offers[&search=<json_encoded key_value_pairs>][no_dupes=true/false]`

### get offer by id
`/index.php?page=api&action=get_offers&id=xxx`

### set offer approved
`/index.php?page=api&action=set_offer_approved&offer_id=xxx`

### set offer rejected
`/index.php?page=api&action=set_offer_rejected&offer_id=xxx`

### set offer deleted
`/index.php?page=api&action=set_offer_deleted&offer_id=xxx`

### set copy offers
`/index.php?page=api&action=set_bulk_copy_offers&offer_ids=<json_encoded_array of offer ids>[&substitutions=<json_encoded_object of key value pairs>]`

### set offer live
`/index.php?page=api&action=set_offer_live&offer_id=xxx&status=(offline|online)`

### set bulk update offers 
`/index.php?page=api&action=set_bulk_update_offers&offer_ids=<json_encoded_array of offer ids>[&substitutions=<json_encoded_object of key value pairs>]`

## Campaigns

### get campaigns
`index.php?page=api&action=get_campaigns`

### set Campaign
`/index.php?page=api&action=set_campaign&campaign=<jsonEncodedObject>`

### get campaign
`index.php?page=api&action=get_campaign&id=xxx`

### delete campaign
`index.php?page=api&action=set_campaign_deleted&id=xxx`


## Packs

### get pack by id
`/index.php?page=api&action=get_pack&offer_id=xxx`

### get pack for umbraco
`/index.php?page=api&action=get_cms_pack&offer_id=xxx`



## misc

### get counties
`/index.php?page=api&action=get_counties`

### get get_categories
`/index.php?page=api&action=get_categories`

### get offer_types
`/index.php?page=api&action=offer_types`

### get report_field
`/index.php?page=api&action=get_report_fields`

### get report
`/index.php?page=api&action=get_report&type=(offer|attraction|user)&selected_ids=<json_array_of_ids>&export_fields=<json_object_sections_and_fields>[&download=true]`

### get logs
`/index.php?page=api&action=get_logs[&search=<json_encoded key_value_pairs]`

### set redemptions
`index.php?page=api&action=set_redemptions&redemptions=[{"offer_id":101,"count":10}, {...}]&date=2023-06-05T16:28:07Z`