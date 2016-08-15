//
//  NItem.h
//  FF
//
//  Created by glority on 13-12-23.
//  Copyright (c) 2013年 glority. All rights reserved.
//

#ifndef __FF__NItem__
#define __FF__NItem__

#include <iostream>
#include <string>
#include "NItemErrorCode.h"

enum FileType
{
    LocalFile,
    LocalFolder,
    CloudFile,
    CloudFolder,
};

enum FileFormat
{
    File,
    Folder,
    CompressedFile,
};

const std::string FSErrorDomain = "com.winzip.FileStructure";

typedef std::function<void()> OnLoadContentCompletion;
typedef std::function<void(long errorCode, std::string message)> OnItemDeleted;
typedef std::function<void(NResultCode)> OnItemRenamed;

class NFolder;

class NItem : public std::enable_shared_from_this<NItem> {
public:
    NItem(std::string path);

    std::string name() { return _name; };
    void set_name(std::string name) { _name = name; };
    std::string path() { return _path; };
    std::weak_ptr<NFolder> parent() { return _parent; };
    void set_parent(std::shared_ptr<NFolder> parent) { _parent = parent; };

    time_t created() { return _created; };
    void set_created(time_t created) { _created = created; };
    time_t modified() { return _modified; };
    void set_modified(time_t modified) { _modified = modified; };
    
    bool contents_loaded() { return _contents_loaded; };
    
    virtual void Delete(OnItemDeleted onItemDeleted) = 0;
    virtual void Rename(std::string name, OnItemRenamed onItemRenamed) = 0;

    virtual std::shared_ptr<NItem> QueryInnerNode(FileFormat format) = 0;
    
    virtual void LoadContent(OnLoadContentCompletion onLoadContentCompletion, bool refresh);

    virtual FileType FileType() = 0;
    
    std::shared_ptr<NItem> get_shared_ptr();

protected:
    std::string _name;
    std::string _path;
    time_t _created;
    time_t _modified;

    std::weak_ptr<NFolder> _parent;
    
    bool _contents_loaded = false;
};

#endif /* defined(__FF__NItem__) */
