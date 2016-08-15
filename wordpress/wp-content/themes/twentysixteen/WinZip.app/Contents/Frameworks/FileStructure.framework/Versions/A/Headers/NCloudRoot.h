//
//  NCloudRoot.h
//  WinZip
//
//  Created by glority on 14-1-10.
//  Copyright (c) 2014年 glority. All rights reserved.
//

#ifndef __WinZip__NCloudRoot__
#define __WinZip__NCloudRoot__

#include <iostream>

#include "NCloudFolder.h"

class NCloudRoot : public NCloudFolder {
    
public:
    NCloudRoot(id service) : NCloudFolder("", nil, service) {};
    void LoadContent(OnLoadContentCompletion onLoadContentCompletion, bool refresh);
};
#endif /* defined(__WinZip__NCloudRoot__) */
