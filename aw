-- OKE DISINI
local HttpService = game:GetService("HttpService")

_G.ConfigFolder = _G.ConfigFolder or "Meng Hub/Config/"

local function CheckFolders()
    local mainFolder = _G.ConfigFolder:split("/")[1]
    if not isfolder(mainFolder) then makefolder(mainFolder) end
    if not isfolder(_G.ConfigFolder) then makefolder(_G.ConfigFolder) end
end

ConfigData = {}
Elements = {} 
CURRENT_VERSION = nil

local function CheckIsPremium()
    return getgenv().MengHubIsPremium
end

function SaveConfig(name)
    CheckFolders()
    local fileName = _G.ConfigFolder .. (name or "Default") .. ".json"
    
    if writefile then
        ConfigData._version = CURRENT_VERSION
        writefile(fileName, HttpService:JSONEncode(ConfigData))
    end
end

function LoadConfigFromFile(name)
    CheckFolders()
    local fileName = _G.ConfigFolder .. (name or "Default") .. ".json"
    
    if isfile and isfile(fileName) then
        local success, result = pcall(function()
            return HttpService:JSONDecode(readfile(fileName))
        end)
        
        if success and type(result) == "table" then
            ConfigData = result
            
            if LoadConfigElements then
                LoadConfigElements()
            end
            return true
        end
    end
    return false
end

function LoadConfigElements()
    for key, element in pairs(Elements) do
        local targetValue = ConfigData[key]
        
        if element.Set then
            if targetValue ~= nil then
                if element.Premium == true or string.find(key, "Premium", 1, true) then
                    if not CheckIsPremium() then
                        targetValue = false
                    end
                end

                -- Colorpicker disimpan sebagai {r,g,b}
                if element.Type == "Colorpicker" then
                    if type(targetValue) == "table" and targetValue.r and targetValue.g and targetValue.b then
                        element:Set(Color3.new(targetValue.r, targetValue.g, targetValue.b))
                    elseif typeof(targetValue) == "Color3" then
                        element:Set(targetValue)
                    end
                else
                    element:Set(targetValue)
                end
            else
                if element.Type == "Toggle" then
                    element:Set(false)
                elseif element.Type == "Slider" then
                    element:Set(element.Default or 0)
                elseif element.Type == "Dropdown" then
                    element:Set(element.Default or "")
                elseif element.Type == "Input" then
                    element:Set("")
                elseif element.Type == "Keybind" then
                    element:Set("None")
                elseif element.Type == "Colorpicker" then
                    -- biarin default yang sudah di-set saat create
                end
            end
        end
    end
end

local Icons = {
    player    = "rbxassetid://12120698352",
    web       = "rbxassetid://137601480983962",
    bag       = "rbxassetid://8601111810",
    shop      = "rbxassetid://4985385964",
    cart      = "rbxassetid://128874923961846",
    plug      = "rbxassetid://137601480983962",
    settings  = "rbxassetid://70386228443175",
    loop      = "rbxassetid://122032243989747",
    gps       = "rbxassetid://17824309485",
    compas    = "rbxassetid://125300760963399",
    gamepad   = "rbxassetid://84173963561612",
    boss      = "rbxassetid://13132186360",
    scroll    = "rbxassetid://114127804740858",
    menu      = "rbxassetid://6340513838",
    crosshair = "rbxassetid://12614416478",
    user      = "rbxassetid://108483430622128",
    stat      = "rbxassetid://12094445329",
    eyes      = "rbxassetid://14321059114",
    sword     = "rbxassetid://82472368671405",
    discord   = "rbxassetid://94434236999817",
    star      = "rbxassetid://107005941750079",
    skeleton  = "rbxassetid://17313330026",
    payment   = "rbxassetid://18747025078",
    scan      = "rbxassetid://109869955247116",
    alert     = "rbxassetid://73186275216515",
    question  = "rbxassetid://17510196486",
    idea      = "rbxassetid://16833255748",
    strom     = "rbxassetid://13321880293",
    dcs       = "rbxassetid://15310731934",
    start     = "rbxassetid://108886429866687",
    next      = "rbxassetid://12662718374",
    rod       = "rbxassetid://103247953194129",
    fish      = "rbxassetid://97167558235554",
    mouse     = "rbxassetid://10088146947"
}

local UserInputService = game:GetService("UserInputService")
local TweenService = game:GetService("TweenService")
local LocalPlayer = game:GetService("Players").LocalPlayer
local Mouse = LocalPlayer:GetMouse()
local CoreGui = game:GetService("CoreGui")
local TextService = game:GetService("TextService")
local viewport = workspace.CurrentCamera.ViewportSize

local function isMobileDevice()
    return UserInputService.TouchEnabled
        and not UserInputService.KeyboardEnabled
        and not UserInputService.MouseEnabled
        and not UserInputService.GamepadEnabled
end

local isMobile = isMobileDevice()

--// PREMIUM TOOLTIP (shared, ngikutin cursor)
local PremiumTooltipGui = Instance.new("ScreenGui")
PremiumTooltipGui.Name = "MenghubPremiumTooltip"
PremiumTooltipGui.ResetOnSpawn = false
PremiumTooltipGui.IgnoreGuiInset = true
PremiumTooltipGui.ZIndexBehavior = Enum.ZIndexBehavior.Sibling
PremiumTooltipGui.Parent = CoreGui

local PremiumTooltip = Instance.new("Frame")
PremiumTooltip.Name = "PremiumTooltip"
PremiumTooltip.BackgroundColor3 = Color3.fromRGB(22, 22, 28)
PremiumTooltip.BorderSizePixel = 0
PremiumTooltip.AutomaticSize = Enum.AutomaticSize.X
PremiumTooltip.Size = UDim2.new(0, 0, 0, 26)
PremiumTooltip.Visible = false
PremiumTooltip.ZIndex = 500
PremiumTooltip.Parent = PremiumTooltipGui

local PTCorner = Instance.new("UICorner")
PTCorner.CornerRadius = UDim.new(0, 6)
PTCorner.Parent = PremiumTooltip

local PTStroke = Instance.new("UIStroke")
PTStroke.Color = Color3.fromRGB(255, 195, 60)
PTStroke.Thickness = 1
PTStroke.Transparency = 0.35
PTStroke.Parent = PremiumTooltip

local PTPadding = Instance.new("UIPadding")
PTPadding.PaddingLeft = UDim.new(0, 10)
PTPadding.PaddingRight = UDim.new(0, 10)
PTPadding.Parent = PremiumTooltip

local PremiumTooltipLabel = Instance.new("TextLabel")
PremiumTooltipLabel.Name = "Label"
PremiumTooltipLabel.BackgroundTransparency = 1
PremiumTooltipLabel.Font = Enum.Font.GothamBold
PremiumTooltipLabel.Text = "Premium Required"
PremiumTooltipLabel.TextColor3 = Color3.fromRGB(255, 195, 60)
PremiumTooltipLabel.TextSize = 12
PremiumTooltipLabel.Size = UDim2.new(0, 0, 1, 0)
PremiumTooltipLabel.AutomaticSize = Enum.AutomaticSize.X
PremiumTooltipLabel.ZIndex = 501
PremiumTooltipLabel.Parent = PremiumTooltip-- Ganti seluruh PremiumTooltip setup jadi ini
local PremiumTooltipGui = Instance.new("ScreenGui")
PremiumTooltipGui.Name = "MenghubPremiumTooltip"
PremiumTooltipGui.ResetOnSpawn = false
PremiumTooltipGui.IgnoreGuiInset = true
PremiumTooltipGui.ZIndexBehavior = Enum.ZIndexBehavior.Global
PremiumTooltipGui.DisplayOrder = 999
PremiumTooltipGui.Parent = CoreGui

local PremiumTooltip = Instance.new("Frame")
PremiumTooltip.Name = "PremiumTooltip"
PremiumTooltip.BackgroundColor3 = Color3.fromRGB(28, 28, 32)
PremiumTooltip.BorderSizePixel = 0
PremiumTooltip.AutomaticSize = Enum.AutomaticSize.XY
PremiumTooltip.Visible = false
PremiumTooltip.ZIndex = 1
PremiumTooltip.Parent = PremiumTooltipGui
Instance.new("UICorner", PremiumTooltip).CornerRadius = UDim.new(0, 6)

local PTStroke = Instance.new("UIStroke")
PTStroke.Color = Color3.fromRGB(60, 60, 70)
PTStroke.Thickness = 1
PTStroke.Transparency = 0
PTStroke.ApplyStrokeMode = Enum.ApplyStrokeMode.Border
PTStroke.Parent = PremiumTooltip

local PTPadding = Instance.new("UIPadding")
PTPadding.PaddingLeft   = UDim.new(0, 10)
PTPadding.PaddingRight  = UDim.new(0, 10)
PTPadding.PaddingTop    = UDim.new(0, 6)
PTPadding.PaddingBottom = UDim.new(0, 6)
PTPadding.Parent = PremiumTooltip

local PremiumTooltipLabel = Instance.new("TextLabel")
PremiumTooltipLabel.Name = "Label"
PremiumTooltipLabel.BackgroundTransparency = 1
PremiumTooltipLabel.Font = Enum.Font.Code
PremiumTooltipLabel.Text = "unlock this with premium"
PremiumTooltipLabel.TextColor3 = Color3.fromRGB(220, 220, 220)
PremiumTooltipLabel.TextSize = 13
PremiumTooltipLabel.Size = UDim2.new(0, 0, 0, 0)
PremiumTooltipLabel.AutomaticSize = Enum.AutomaticSize.XY
PremiumTooltipLabel.ZIndex = 2
PremiumTooltipLabel.Parent = PremiumTooltip

local premiumTooltipMoveConn = nil

local function UpdatePremiumTooltipPos(x, y)
    local tooltipW = PremiumTooltip.AbsoluteSize.X
    local tooltipH = PremiumTooltip.AbsoluteSize.Y
    local px = x + 14
    local py = y + 20  -- di BAWAH cursor, bukan di atas
    if px + tooltipW > viewport.X - 5 then
        px = viewport.X - tooltipW - 5
    end
    if py + tooltipH > viewport.Y - 5 then
        py = y - tooltipH - 8  -- kalau ga muat di bawah, baru taruh di atas
    end
    PremiumTooltip.Position = UDim2.new(0, px, 0, py)
end

local function ShowPremiumTooltip()
    local mp = UserInputService:GetMouseLocation()
    PremiumTooltip.Visible = true
    UpdatePremiumTooltipPos(mp.X, mp.Y)
    if premiumTooltipMoveConn then premiumTooltipMoveConn:Disconnect() end
    premiumTooltipMoveConn = UserInputService.InputChanged:Connect(function(input)
        if input.UserInputType == Enum.UserInputType.MouseMovement
        or input.UserInputType == Enum.UserInputType.Touch then
            UpdatePremiumTooltipPos(input.Position.X, input.Position.Y)
        end
    end)
end

local function HidePremiumTooltip()
    PremiumTooltip.Visible = false
    if premiumTooltipMoveConn then
        premiumTooltipMoveConn:Disconnect()
        premiumTooltipMoveConn = nil
    end
end

local function AttachPremiumLock(button, isPremiumFeature)
    if not isPremiumFeature then return end

    if CheckIsPremium() then
        return
    end

    button.MouseEnter:Connect(function()
        if not CheckIsPremium() then 
            ShowPremiumTooltip() 
        end
    end)
    button.MouseLeave:Connect(HidePremiumTooltip)
end

local function safeSize(pxWidth, pxHeight)
    local scaleX = pxWidth / viewport.X
    local scaleY = pxHeight / viewport.Y

    if isMobile then
        if scaleX > 0.5 then scaleX = 0.5 end
        if scaleY > 0.3 then scaleY = 0.3 end
    end

    return UDim2.new(scaleX, 0, scaleY, 0)
end

local isDraggingLocked = false
local function MakeDraggable(topbarobject, object)
    local function CustomPos(topbarobject, object)
        local Dragging, DragInput, DragStart, StartPosition

        local function UpdatePos(input)
            local Delta = input.Position - DragStart
            local pos = UDim2.new(
                StartPosition.X.Scale,
                StartPosition.X.Offset + Delta.X,
                StartPosition.Y.Scale,
                StartPosition.Y.Offset + Delta.Y
            )
            object.Position = pos
        end

        topbarobject.InputBegan:Connect(function(input)
            if input.UserInputType == Enum.UserInputType.MouseButton1 or input.UserInputType == Enum.UserInputType.Touch then
                Dragging = true
                DragStart = input.Position
                StartPosition = object.Position
                input.Changed:Connect(function()
                    if input.UserInputState == Enum.UserInputState.End then
                        Dragging = false
                    end
                end)
            end
        end)

        topbarobject.InputChanged:Connect(function(input)
            if input.UserInputType == Enum.UserInputType.MouseMovement or input.UserInputType == Enum.UserInputType.Touch then
                DragInput = input
            end
        end)

        UserInputService.InputChanged:Connect(function(input)
            if input == DragInput and Dragging then
                UpdatePos(input)
            end
        end)
    end

    local function CustomSize(object)
        local Dragging, DragInput, DragStart, StartSize

        local minSizeX, minSizeY
        local defSizeX, defSizeY

        if isMobile then
            minSizeX, minSizeY = 100, 100
            defSizeX, defSizeY = 470, 270
        else
            minSizeX, minSizeY = 100, 100
            defSizeX, defSizeY = 586, 364
        end

        object.Size = UDim2.new(0, defSizeX, 0, defSizeY)

        local changesizeobject = Instance.new("Frame")
        changesizeobject.AnchorPoint = Vector2.new(1, 1)
        changesizeobject.BackgroundTransparency = 1
        changesizeobject.Size = UDim2.new(0, 40, 0, 40)
        changesizeobject.Position = UDim2.new(1, 20, 1, 20)
        changesizeobject.Name = "changesizeobject"
        changesizeobject.Parent = object

        local function UpdateSize(input)
            local Delta = input.Position - DragStart
            local newWidth = StartSize.X.Offset + Delta.X
            local newHeight = StartSize.Y.Offset + Delta.Y

            newWidth = math.max(newWidth, minSizeX)
            newHeight = math.max(newHeight, minSizeY)

            local Tween = TweenService:Create(object, TweenInfo.new(0.2), { Size = UDim2.new(0, newWidth, 0, newHeight) })
            Tween:Play()
        end

        changesizeobject.InputBegan:Connect(function(input)
            if input.UserInputType == Enum.UserInputType.MouseButton1 or input.UserInputType == Enum.UserInputType.Touch then
                Dragging = true
                DragStart = input.Position
                StartSize = object.Size
                input.Changed:Connect(function()
                    if input.UserInputState == Enum.UserInputState.End then
                        Dragging = false
                    end
                end)
            end
        end)

        changesizeobject.InputChanged:Connect(function(input)
            if input.UserInputType == Enum.UserInputType.MouseMovement or input.UserInputType == Enum.UserInputType.Touch then
                DragInput = input
            end
        end)

        UserInputService.InputChanged:Connect(function(input)
            if input == DragInput and Dragging then
                UpdateSize(input)
            end
        end)
    end

    CustomSize(object)
    CustomPos(topbarobject, object)
end

function CircleClick(Button, X, Y)
    spawn(function()
        Button.ClipsDescendants = true
        local Circle = Instance.new("ImageLabel")
        Circle.Image = "rbxassetid://266543268"
        Circle.ImageColor3 = Color3.fromRGB(80, 80, 80)
        Circle.ImageTransparency = 0.8999999761581421
        Circle.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
        Circle.BackgroundTransparency = 1
        Circle.ZIndex = 10
        Circle.Name = "Circle"
        Circle.Parent = Button

        local NewX = X - Circle.AbsolutePosition.X
        local NewY = Y - Circle.AbsolutePosition.Y
        Circle.Position = UDim2.new(0, NewX, 0, NewY)
        local Size = 0
        if Button.AbsoluteSize.X > Button.AbsoluteSize.Y then
            Size = Button.AbsoluteSize.X * 1.5
        elseif Button.AbsoluteSize.X < Button.AbsoluteSize.Y then
            Size = Button.AbsoluteSize.Y * 1.5
        elseif Button.AbsoluteSize.X == Button.AbsoluteSize.Y then
            Size = Button.AbsoluteSize.X * 1.5
        end

        local Time = 0.5
        Circle:TweenSizeAndPosition(UDim2.new(0, Size, 0, Size), UDim2.new(0.5, -Size / 2, 0.5, -Size / 2), "Out", "Quad",
            Time, false, nil)
        for i = 1, 10 do
            Circle.ImageTransparency = Circle.ImageTransparency + 0.01
            wait(Time / 10)
        end
        Circle:Destroy()
    end)
end

local Menghub = {}
function Menghub:MakeNotify(NotifyConfig)
    local NotifyConfig = NotifyConfig or {}
    NotifyConfig.Title = NotifyConfig.Title or "Meng Hub"
    NotifyConfig.Time = NotifyConfig.Time or 0.4
    NotifyConfig.Delay = NotifyConfig.Delay or 3

    local NotifyFunction = {}

    local BAR_COLOR = Color3.fromRGB(170, 120, 255)

    local PADDING_X = 14
    local MIN_WIDTH = 115
    local MAX_WIDTH = 370
    local TOP_OFFSET = 28   -- DINAIIKKAN LAGI (sangat atas)

    spawn(function()
        if not CoreGui:FindFirstChild("NotifyGui") then
            local sg = Instance.new("ScreenGui")
            sg.Name = "NotifyGui"
            sg.ZIndexBehavior = Enum.ZIndexBehavior.Sibling
            sg.Parent = CoreGui
        end

        if not CoreGui.NotifyGui:FindFirstChild("NotifyLayout") then
            local layout = Instance.new("Frame")
            layout.Name = "NotifyLayout"
            layout.BackgroundTransparency = 1
            layout.AnchorPoint = Vector2.new(1, 0)
            layout.Position = UDim2.new(1, -18, 0, TOP_OFFSET)
            layout.Size = UDim2.new(0, 390, 1, -TOP_OFFSET)
            layout.Parent = CoreGui.NotifyGui

            layout.ChildRemoved:Connect(function()
                local children = layout:GetChildren()
                table.sort(children, function(a,b) return a.Position.Y.Offset < b.Position.Y.Offset end)
                local y = 0
                for _, v in children do
                    if v.Name == "NotifyFrame" then
                        TweenService:Create(v, TweenInfo.new(0.25, Enum.EasingStyle.Quad), {Position = UDim2.new(1,0,0,y)}):Play()
                        y += v.Size.Y.Offset + 5
                    end
                end
            end)
        end

        local NotifyPosY = 0
        for _, v in CoreGui.NotifyGui.NotifyLayout:GetChildren() do
            if v.Name == "NotifyFrame" then
                NotifyPosY += v.Size.Y.Offset + 5
            end
        end

        local NotifyFrame = Instance.new("Frame")
        NotifyFrame.Name = "NotifyFrame"
        NotifyFrame.BackgroundTransparency = 1
        NotifyFrame.Size = UDim2.new(0, MIN_WIDTH, 0, 36)
        NotifyFrame.Position = UDim2.new(1, 0, 0, NotifyPosY)
        NotifyFrame.AnchorPoint = Vector2.new(1, 0)
        NotifyFrame.Parent = CoreGui.NotifyGui.NotifyLayout

        -- Hitung ukuran teks
        local BoundsParams = Instance.new("GetTextBoundsParams")
        BoundsParams.Text = NotifyConfig.Title
        BoundsParams.Font = Font.new("Code")
        BoundsParams.Size = 13
        BoundsParams.Width = MAX_WIDTH

        local success, bounds = pcall(function() return TextService:GetTextBoundsAsync(BoundsParams) end)
        local textWidth = success and bounds.X or (#NotifyConfig.Title * 7.7)
        local boxWidth = math.clamp(textWidth + PADDING_X * 2, MIN_WIDTH, MAX_WIDTH)

        NotifyFrame.Size = UDim2.new(0, boxWidth, 0, 36)

        -- Main Panel
        local Main = Instance.new("Frame")
        Main.Name = "NotifyFrameReal"
        Main.BackgroundColor3 = Color3.fromRGB(28, 28, 28)
        Main.Size = UDim2.new(1, 0, 1, 0)
        Main.Position = UDim2.new(0, boxWidth + 20, 0, 0)
        Main.Parent = NotifyFrame

        local Corner = Instance.new("UICorner")
        Corner.CornerRadius = UDim.new(0, 4)
        Corner.Parent = Main

        local Stroke = Instance.new("UIStroke")
        Stroke.Color = Color3.fromRGB(53, 53, 53)
        Stroke.Thickness = 1
        Stroke.Transparency = 0.6
        Stroke.Parent = Main

        -- Title
        local Title = Instance.new("TextLabel")
        Title.Font = Enum.Font.Code
        Title.Text = NotifyConfig.Title
        Title.TextColor3 = Color3.fromRGB(255, 255, 255)
        Title.TextSize = 13
        Title.TextXAlignment = Enum.TextXAlignment.Center
        Title.BackgroundTransparency = 1
        Title.Position = UDim2.new(0, 0, 0, 7)
        Title.Size = UDim2.new(1, 0, 0, 16)
        Title.Parent = Main

        -- Progress Bar
        local ProgBack = Instance.new("Frame")
        ProgBack.BackgroundColor3 = Color3.fromRGB(35, 35, 35)
        ProgBack.Size = UDim2.new(1, -12, 0, 3)
        ProgBack.Position = UDim2.new(0.5, 0, 1, -5)
        ProgBack.AnchorPoint = Vector2.new(0.5, 1)
        ProgBack.Parent = Main

        Instance.new("UICorner", ProgBack).CornerRadius = UDim.new(1,0)

        local ProgBar = Instance.new("Frame")
        ProgBar.BackgroundColor3 = BAR_COLOR
        ProgBar.Size = UDim2.new(1,0,1,0)
        ProgBar.Parent = ProgBack

        Instance.new("UICorner", ProgBar).CornerRadius = UDim.new(1,0)

        -- Animation
        TweenService:Create(Main, TweenInfo.new(NotifyConfig.Time, Enum.EasingStyle.Quint, Enum.EasingDirection.Out), {
            Position = UDim2.new(0, 0, 0, 0)
        }):Play()

        TweenService:Create(ProgBar, TweenInfo.new(NotifyConfig.Delay, Enum.EasingStyle.Linear), {
            Size = UDim2.new(0, 0, 1, 0)
        }):Play()

        task.wait(NotifyConfig.Delay)
        
        TweenService:Create(Main, TweenInfo.new(NotifyConfig.Time, Enum.EasingStyle.Quint, Enum.EasingDirection.In), {
            Position = UDim2.new(0, boxWidth + 20, 0, 0)
        }):Play()
        
        task.wait(NotifyConfig.Time)
        NotifyFrame:Destroy()
    end)

    return NotifyFunction
end

function notif(msg, delay)
    return Menghub:MakeNotify({
        Title = msg or "Meng Hub",
        Delay = delay or 3
    })
end

function Menghub:Window(GuiConfig)
    GuiConfig              = GuiConfig or {}
    GuiConfig.Title        = GuiConfig.Title or "Meng Hub"
    GuiConfig.Footer       = GuiConfig.Footer or "MengHub >:D"
    GuiConfig.Color        = GuiConfig.Color or Color3.fromRGB(255, 0, 255)
    GuiConfig["Tab Width"] = GuiConfig["Tab Width"] or 120
    GuiConfig.Version      = GuiConfig.Version or 1
    GuiConfig.Icon         = GuiConfig.Icon or "rbxassetid://80659354137631"

    CURRENT_VERSION        = GuiConfig.Version

    local GuiFunc = {}

    local Menghubb = Instance.new("ScreenGui");
    local DropShadowHolder = Instance.new("Frame");
    local DropShadow = Instance.new("ImageLabel");
    local Main = Instance.new("Frame");
    local UICorner = Instance.new("UICorner");
    local Top = Instance.new("Frame");
    local TextLabel = Instance.new("TextLabel");
    local UICorner1 = Instance.new("UICorner");
    local TextLabel1 = Instance.new("TextLabel");
    local TitleIcon = Instance.new("ImageLabel");
    local Close = Instance.new("TextButton");
    local ImageLabel1 = Instance.new("ImageLabel");
    local Min = Instance.new("TextButton");
    local ImageLabel2 = Instance.new("ImageLabel");
    local LayersTab = Instance.new("Frame");
    local UICorner2 = Instance.new("UICorner");
    local DecideFrame = Instance.new("Frame");
    local Layers = Instance.new("Frame");
    local UICorner6 = Instance.new("UICorner");
    local NameTab = Instance.new("TextLabel");
    local LayersReal = Instance.new("Frame");
    local LayersFolder = Instance.new("Folder");
    local LayersPageLayout = Instance.new("UIPageLayout");
    local MainStroke = Instance.new("UIStroke");

    Menghubb.ZIndexBehavior = Enum.ZIndexBehavior.Sibling
    Menghubb.Name = "MengHubGui"
    Menghubb.ResetOnSpawn = false
    Menghubb.Parent = game:GetService("CoreGui")

    DropShadowHolder.BackgroundTransparency = 1
    DropShadowHolder.BorderSizePixel = 0
    DropShadowHolder.AnchorPoint = Vector2.new(0.5, 0.5)
    DropShadowHolder.Position = UDim2.new(0.5, 0, 0.5, 0)
    if isMobile then
        DropShadowHolder.Size = safeSize(470, 270)
    else
        DropShadowHolder.Size = safeSize(640, 400)
    end
    DropShadowHolder.ZIndex = 0
    DropShadowHolder.Name = "DropShadowHolder"
    DropShadowHolder.Parent = Menghubb

    DropShadowHolder.Position = UDim2.new(0, (Menghubb.AbsoluteSize.X // 2 - DropShadowHolder.Size.X.Offset // 2), 0,
        (Menghubb.AbsoluteSize.Y // 2 - DropShadowHolder.Size.Y.Offset // 2))
    DropShadow.Image = "rbxassetid://6015897843"
    DropShadow.ImageColor3 = Color3.fromRGB(15, 15, 15)
    DropShadow.ImageTransparency = 1
    DropShadow.ScaleType = Enum.ScaleType.Slice
    DropShadow.SliceCenter = Rect.new(49, 49, 450, 450)
    DropShadow.AnchorPoint = Vector2.new(0.5, 0.5)
    DropShadow.BackgroundTransparency = 1
    DropShadow.BorderSizePixel = 0
    DropShadow.Position = UDim2.new(0.5, 0, 0.5, 0)
    DropShadow.Size = UDim2.new(1, 47, 1, 47)
    DropShadow.ZIndex = 0
    DropShadow.Name = "DropShadow"
    DropShadow.Parent = DropShadowHolder

    if GuiConfig.Theme then
        Main:Destroy()
        Main = Instance.new("ImageLabel")
        Main.Image = "rbxassetid://" .. GuiConfig.Theme
        Main.ScaleType = Enum.ScaleType.Crop
        Main.BackgroundTransparency = 1
        Main.ImageTransparency = GuiConfig.ThemeTransparency or 0.15
    else
        Main.BackgroundColor3 = Color3.fromRGB(0, 0, 0)
        Main.BackgroundTransparency = 0.15
    end

    Main.AnchorPoint = Vector2.new(0.5, 0.5)
    Main.BorderColor3 = Color3.fromRGB(0, 0, 0)
    Main.BorderSizePixel = 0
    Main.Position = UDim2.new(0.5, 0, 0.5, 0)
    Main.Size = UDim2.new(1, -47, 1, -47)
    Main.Name = "Main"
    Main.Parent = DropShadow

    MainStroke.Thickness = 1.2
    MainStroke.Color = Color3.fromRGB(189, 162, 241)
    MainStroke.Transparency = 0.6
    MainStroke.ApplyStrokeMode = Enum.ApplyStrokeMode.Border
    MainStroke.Parent = Main

    UICorner.Parent = Main

    Top.BackgroundColor3 = Color3.fromRGB(0, 0, 0)
    Top.BackgroundTransparency = 0.9990000128746033
    Top.BorderColor3 = Color3.fromRGB(0, 0, 0)
    Top.BorderSizePixel = 0
    Top.Size = UDim2.new(1, 0, 0, 38)
    Top.Name = "Top"
    Top.Active = true
    Top.Parent = Main

    TitleIcon.Name = "TitleIcon"
    TitleIcon.Parent = Top
    TitleIcon.BackgroundTransparency = 1
    TitleIcon.BorderSizePixel = 0
    TitleIcon.AnchorPoint = Vector2.new(0, 0.5)
    TitleIcon.Position = UDim2.new(0, 10, 0.5, 0) 
    TitleIcon.Size = UDim2.new(0, 20, 0, 20)
    TitleIcon.Image = GuiConfig.Icon

    -- // Background Image
    local ImageWrapper = Instance.new("Frame")
    ImageWrapper.Name = "ImageWrapper"
    ImageWrapper.Parent = Main
    ImageWrapper.BackgroundTransparency = 1
    ImageWrapper.Size = UDim2.new(1, 0, 1, 0)
    ImageWrapper.Position = UDim2.new(0, 0, 0, 0)
    ImageWrapper.ZIndex = 0
    ImageWrapper.ClipsDescendants = true

    local ThemeImage = Instance.new("ImageLabel")
    ThemeImage.Name = "ThemeImage"
    ThemeImage.Parent = ImageWrapper
    ThemeImage.BackgroundTransparency = 1
    ThemeImage.AnchorPoint = Vector2.new(1, 1)
    ThemeImage.Position = UDim2.new(1, 0, 1, 0)
    ThemeImage.Size = UDim2.new(0.65, 0, 0.95, 0)
    ThemeImage.ZIndex = 1
    ThemeImage.Image = "rbxassetid://99142076352082"
    ThemeImage.ImageTransparency = 0.15
    ThemeImage.ScaleType = Enum.ScaleType.Crop

    local Gradient = Instance.new("UIGradient")
    Gradient.Rotation = 45
    Gradient.Transparency = NumberSequence.new({
        NumberSequenceKeypoint.new(0, 1),
        NumberSequenceKeypoint.new(0.6, 0.8),
        NumberSequenceKeypoint.new(1, 0)
    })
    Gradient.Parent = ThemeImage

    -- Title + Footer dalam satu row pakai UIListLayout biar ga pernah dempet
    local TitleRow = Instance.new("Frame")
    TitleRow.Name = "TitleRow"
    TitleRow.BackgroundTransparency = 1
    TitleRow.BorderSizePixel = 0
    TitleRow.AnchorPoint = Vector2.new(0, 0.5)
    TitleRow.Position = UDim2.new(0, 35, 0.5, 0)
    TitleRow.Size = UDim2.new(1, -145, 0, 20)
    TitleRow.AutomaticSize = Enum.AutomaticSize.X
    TitleRow.ClipsDescendants = false
    TitleRow.Parent = Top

    local TitleRowLayout = Instance.new("UIListLayout")
    TitleRowLayout.FillDirection = Enum.FillDirection.Horizontal
    TitleRowLayout.VerticalAlignment = Enum.VerticalAlignment.Center
    TitleRowLayout.HorizontalAlignment = Enum.HorizontalAlignment.Left
    TitleRowLayout.Padding = UDim.new(0, 6)
    TitleRowLayout.SortOrder = Enum.SortOrder.LayoutOrder
    TitleRowLayout.Parent = TitleRow

    -- Title label
    TextLabel.Font = Enum.Font.GothamBold
    TextLabel.Text = GuiConfig.Title
    TextLabel.TextColor3 = GuiConfig.Color
    TextLabel.TextSize = 14
    TextLabel.TextXAlignment = Enum.TextXAlignment.Left
    TextLabel.BackgroundTransparency = 1
    TextLabel.BorderSizePixel = 0
    TextLabel.Size = UDim2.new(0, 0, 1, 0)
    TextLabel.AutomaticSize = Enum.AutomaticSize.X
    TextLabel.LayoutOrder = 0
    TextLabel.Parent = TitleRow

    UICorner1.Parent = Top

    -- Separator
    local TitleSep = Instance.new("TextLabel")
    TitleSep.Name = "TitleSep"
    TitleSep.Text = "|"
    TitleSep.Font = Enum.Font.Gotham
    TitleSep.TextSize = 12
    TitleSep.TextColor3 = Color3.fromRGB(120, 100, 150)
    TitleSep.BackgroundTransparency = 1
    TitleSep.BorderSizePixel = 0
    TitleSep.Size = UDim2.new(0, 8, 1, 0)
    TitleSep.AutomaticSize = Enum.AutomaticSize.X
    TitleSep.LayoutOrder = 1
    TitleSep.Parent = TitleRow

    -- Footer label
    TextLabel1.Font = Enum.Font.Gotham
    TextLabel1.Text = GuiConfig.Footer
    TextLabel1.TextColor3 = Color3.fromRGB(160, 140, 190)
    TextLabel1.TextSize = 12
    TextLabel1.TextXAlignment = Enum.TextXAlignment.Left
    TextLabel1.BackgroundTransparency = 1
    TextLabel1.BorderSizePixel = 0
    TextLabel1.Size = UDim2.new(0, 0, 1, 0)
    TextLabel1.AutomaticSize = Enum.AutomaticSize.X
    TextLabel1.LayoutOrder = 2
    TextLabel1.Parent = TitleRow

    Close.Font = Enum.Font.SourceSans
    Close.Text = ""
    Close.TextColor3 = Color3.fromRGB(0, 0, 0)
    Close.TextSize = 14
    Close.AnchorPoint = Vector2.new(1, 0.5)
    Close.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    Close.BackgroundTransparency = 0.9990000128746033
    Close.BorderColor3 = Color3.fromRGB(0, 0, 0)
    Close.BorderSizePixel = 0
    Close.Position = UDim2.new(1, -8, 0.5, 0)
    Close.Size = UDim2.new(0, 25, 0, 25)
    Close.Name = "Close"
    Close.Parent = Top

    ImageLabel1.Image = "rbxassetid://9886659671"
    ImageLabel1.AnchorPoint = Vector2.new(0.5, 0.5)
    ImageLabel1.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    ImageLabel1.BackgroundTransparency = 0.9990000128746033
    ImageLabel1.BorderColor3 = Color3.fromRGB(0, 0, 0)
    ImageLabel1.BorderSizePixel = 0
    ImageLabel1.Position = UDim2.new(0.49, 0, 0.5, 0)
    ImageLabel1.Size = UDim2.new(1, -8, 1, -8)
    ImageLabel1.Parent = Close

    Min.Font = Enum.Font.SourceSans
    Min.Text = ""
    Min.TextColor3 = Color3.fromRGB(0, 0, 0)
    Min.TextSize = 14
    Min.AnchorPoint = Vector2.new(1, 0.5)
    Min.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    Min.BackgroundTransparency = 0.9990000128746033
    Min.BorderColor3 = Color3.fromRGB(0, 0, 0)
    Min.BorderSizePixel = 0
    Min.Position = UDim2.new(1, -38, 0.5, 0)
    Min.Size = UDim2.new(0, 25, 0, 25)
    Min.Name = "Min"
    Min.Parent = Top

    ImageLabel2.Image = "rbxassetid://9886659276"
    ImageLabel2.AnchorPoint = Vector2.new(0.5, 0.5)
    ImageLabel2.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    ImageLabel2.BackgroundTransparency = 0.9990000128746033
    ImageLabel2.ImageTransparency = 0.2
    ImageLabel2.BorderColor3 = Color3.fromRGB(0, 0, 0)
    ImageLabel2.BorderSizePixel = 0
    ImageLabel2.Position = UDim2.new(0.5, 0, 0.5, 0)
    ImageLabel2.Size = UDim2.new(1, -9, 1, -9)
    ImageLabel2.Parent = Min

    LayersTab.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    LayersTab.BackgroundTransparency = 0.9990000128746033
    LayersTab.BorderColor3 = Color3.fromRGB(0, 0, 0)
    LayersTab.BorderSizePixel = 0
    LayersTab.Position = UDim2.new(0, 9, 0, 50)
    LayersTab.Size = UDim2.new(0, GuiConfig["Tab Width"], 1, -59)
    LayersTab.Name = "LayersTab"
    LayersTab.Parent = Main

    UICorner2.CornerRadius = UDim.new(0, 2)
    UICorner2.Parent = LayersTab

    DecideFrame.AnchorPoint = Vector2.new(0.5, 0)
    DecideFrame.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    DecideFrame.BackgroundTransparency = 0.85
    DecideFrame.BorderColor3 = Color3.fromRGB(0, 0, 0)
    DecideFrame.BorderSizePixel = 0
    DecideFrame.Position = UDim2.new(0.5, 0, 0, 38)
    DecideFrame.Size = UDim2.new(1, 0, 0, 1)
    DecideFrame.Name = "DecideFrame"
    DecideFrame.Parent = Main

    Layers.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    Layers.BackgroundTransparency = 0.9990000128746033
    Layers.BorderColor3 = Color3.fromRGB(0, 0, 0)
    Layers.BorderSizePixel = 0
    Layers.Position = UDim2.new(0, GuiConfig["Tab Width"] + 18, 0, 50)
    Layers.Size = UDim2.new(1, -(GuiConfig["Tab Width"] + 9 + 18), 1, -59)
    Layers.Name = "Layers"
    Layers.Parent = Main

    UICorner6.CornerRadius = UDim.new(0, 2)
    UICorner6.Parent = Layers

    -- NameTab disembunyikan (tidak dipakai lagi)
    NameTab.Visible = false
    NameTab.Size = UDim2.new(1, 0, 0, 0)
    NameTab.Name = "NameTab"
    NameTab.Parent = Layers

    -- LayersReal sekarang mengisi penuh karena NameTab disembunyikan
    LayersReal.AnchorPoint = Vector2.new(0, 0)
    LayersReal.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    LayersReal.BackgroundTransparency = 0.9990000128746033
    LayersReal.BorderColor3 = Color3.fromRGB(0, 0, 0)
    LayersReal.BorderSizePixel = 0
    LayersReal.ClipsDescendants = true
    LayersReal.Position = UDim2.new(0, 0, 0, 0)
    LayersReal.Size = UDim2.new(1, 0, 1, 0)
    LayersReal.Name = "LayersReal"
    LayersReal.Parent = Layers

    LayersFolder.Name = "LayersFolder"
    LayersFolder.Parent = LayersReal

    local TabSwitchOverlay = Instance.new("Frame")
    TabSwitchOverlay.Name = "TabSwitchOverlay"
    TabSwitchOverlay.Size = UDim2.new(1, 0, 1, 0)
    TabSwitchOverlay.Position = UDim2.new(0, 0, 0, 0)
    TabSwitchOverlay.BackgroundColor3 = Color3.fromRGB(10, 10, 15)
    TabSwitchOverlay.BackgroundTransparency = 1
    TabSwitchOverlay.BorderSizePixel = 0
    TabSwitchOverlay.ZIndex = 50
    TabSwitchOverlay.Parent = LayersReal

    LayersPageLayout.SortOrder = Enum.SortOrder.LayoutOrder
    LayersPageLayout.Name = "LayersPageLayout"
    LayersPageLayout.Parent = LayersFolder
    LayersPageLayout.TweenTime = 0
    LayersPageLayout.EasingDirection = Enum.EasingDirection.InOut
    LayersPageLayout.EasingStyle = Enum.EasingStyle.Quad

    local PlayerFooter = Instance.new("Frame")
    PlayerFooter.Name = "PlayerFooter"
    PlayerFooter.AnchorPoint = Vector2.new(0, 1)
    PlayerFooter.BackgroundTransparency = 1
    PlayerFooter.BorderSizePixel = 0
    PlayerFooter.Position = UDim2.new(0, 3, 1, -3)
    PlayerFooter.Size = UDim2.new(1, -18, 0, 40)
    PlayerFooter.Parent = LayersTab
    PlayerFooter.ZIndex = 100

    local PlayerAvatar = Instance.new("ImageLabel")
    PlayerAvatar.Name = "PlayerAvatar"
    PlayerAvatar.BackgroundColor3 = Color3.fromRGB(40, 40, 40)
    PlayerAvatar.BackgroundTransparency = 0.2
    PlayerAvatar.BorderSizePixel = 0
    PlayerAvatar.AnchorPoint = Vector2.new(0, 0.5)
    PlayerAvatar.Position = UDim2.new(0, 0, 0.5, 2)
    PlayerAvatar.Size = UDim2.new(0, 26, 0, 26)
    PlayerAvatar.Image = "rbxthumb://type=AvatarHeadShot&id=" .. game.Players.LocalPlayer.UserId .. "&w=150&h=150"
    PlayerAvatar.Parent = PlayerFooter

    local AvatarCorner = Instance.new("UICorner")
    AvatarCorner.CornerRadius = UDim.new(1, 0)
    AvatarCorner.Parent = PlayerAvatar

    local AvatarStroke = Instance.new("UIStroke")
    AvatarStroke.Color = GuiConfig.Color
    AvatarStroke.Thickness = 1.2
    AvatarStroke.Transparency = 0.5
    AvatarStroke.Parent = PlayerAvatar

    local PlayerName = Instance.new("TextLabel")
    PlayerName.Name = "PlayerName"
    PlayerName.Font = Enum.Font.GothamBold

    local displayName = game.Players.LocalPlayer.Name
    local shortName = displayName
    if #displayName > 3 then
        shortName = string.sub(displayName, 1, 3) .. "***"
    end
    PlayerName.Text = "Welcome, " .. shortName

    PlayerName.TextColor3 = Color3.fromRGB(180, 180, 180)
    PlayerName.TextSize = 11
    PlayerName.TextXAlignment = Enum.TextXAlignment.Left
    PlayerName.BackgroundTransparency = 1
    PlayerName.Position = UDim2.new(0, 38, 0, 0)
    PlayerName.Size = UDim2.new(1, -38, 1, 0)
    PlayerName.TextTruncate = Enum.TextTruncate.None
    PlayerName.Parent = PlayerFooter

    local ScrollTab = Instance.new("ScrollingFrame");
    local UIListLayout = Instance.new("UIListLayout");

    local SearchBarFrame = Instance.new("Frame")
    SearchBarFrame.Name = "SearchBarFrame"
    SearchBarFrame.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    SearchBarFrame.BackgroundTransparency = 0.93
    SearchBarFrame.BorderSizePixel = 0
    SearchBarFrame.Position = UDim2.new(0, 4, 0, 3)
    SearchBarFrame.Size = UDim2.new(1, -8, 0, 26)
    SearchBarFrame.Parent = LayersTab

    local SearchCorner = Instance.new("UICorner")
    SearchCorner.CornerRadius = UDim.new(0, 6)
    SearchCorner.Parent = SearchBarFrame

    local PillStroke = Instance.new("UIStroke")
    PillStroke.Color = GuiConfig.Color
    PillStroke.Thickness = 1
    PillStroke.Transparency = 0.7
    PillStroke.ApplyStrokeMode = Enum.ApplyStrokeMode.Border
    PillStroke.Parent = SearchBarFrame

    local SearchIcon = Instance.new("ImageLabel")
    SearchIcon.Image = "rbxassetid://109869955247116"
    SearchIcon.ImageColor3 = GuiConfig.Color
    SearchIcon.ImageTransparency = 0.3
    SearchIcon.BackgroundTransparency = 1
    SearchIcon.AnchorPoint = Vector2.new(0, 0.5)
    SearchIcon.Position = UDim2.new(0, 7, 0.5, 0)
    SearchIcon.Size = UDim2.new(0, 13, 0, 13)
    SearchIcon.Parent = SearchBarFrame

    local SearchInput = Instance.new("TextBox")
    SearchInput.PlaceholderText = "Search..."
    SearchInput.PlaceholderColor3 = Color3.fromRGB(120, 120, 120)
    SearchInput.Text = ""
    SearchInput.Font = Enum.Font.GothamBold
    SearchInput.TextSize = 11
    SearchInput.TextColor3 = Color3.fromRGB(220, 220, 220)
    SearchInput.BackgroundTransparency = 1
    SearchInput.TextXAlignment = Enum.TextXAlignment.Left
    SearchInput.ClearTextOnFocus = false
    SearchInput.Position = UDim2.new(0, 24, 0, 0)
    SearchInput.Size = UDim2.new(1, -30, 1, 0)
    SearchInput.Parent = SearchBarFrame

    SearchInput.Focused:Connect(function()
        TweenService:Create(PillStroke, TweenInfo.new(0.2), { Transparency = 0 }):Play()
        TweenService:Create(SearchBarFrame, TweenInfo.new(0.2), { BackgroundTransparency = 0.85 }):Play()
    end)

    SearchInput.FocusLost:Connect(function()
        TweenService:Create(PillStroke, TweenInfo.new(0.2), { Transparency = 0.7 }):Play()
        TweenService:Create(SearchBarFrame, TweenInfo.new(0.2), { BackgroundTransparency = 0.93 }):Play()
    end)

    local function GetTabName(layoutOrder)
        for _, tab in pairs(ScrollTab:GetChildren()) do
            if tab:IsA("Frame") and tab.LayoutOrder == layoutOrder then
                local tabNameObj = tab:FindFirstChild("TabName")
                if tabNameObj then return tabNameObj.Text end
            end
        end
        return "Unknown"
    end

    SearchInput:GetPropertyChangedSignal("Text"):Connect(function()
        local query = string.lower(SearchInput.Text)
        
        local old = LayersTab:FindFirstChild("SearchDropdown")
        if old then old:Destroy() end

        if query == "" then return end

        local Dropdown = Instance.new("ScrollingFrame")
        Dropdown.Name = "SearchDropdown"
        Dropdown.BackgroundColor3 = Color3.fromRGB(18, 18, 24)
        Dropdown.BackgroundTransparency = 0.05
        Dropdown.BorderSizePixel = 0
        Dropdown.Position = UDim2.new(0, 4, 0, 38)
        Dropdown.Size = UDim2.new(1, -8, 0, 0)
        Dropdown.ZIndex = 150
        Dropdown.ScrollBarThickness = 2
        Dropdown.ScrollBarImageColor3 = GuiConfig.Color
        Dropdown.ClipsDescendants = true
        Dropdown.Parent = LayersTab

        Instance.new("UICorner", Dropdown).CornerRadius = UDim.new(0, 6)

        local DropStroke = Instance.new("UIStroke")
        DropStroke.Color = Color3.fromRGB(60, 60, 70)
        DropStroke.Thickness = 1
        DropStroke.ApplyStrokeMode = Enum.ApplyStrokeMode.Border
        DropStroke.Parent = Dropdown

        local List = Instance.new("UIListLayout")
        List.Padding = UDim.new(0, 2)
        List.SortOrder = Enum.SortOrder.LayoutOrder
        List.Parent = Dropdown

        local Padding = Instance.new("UIPadding")
        Padding.PaddingTop = UDim.new(0, 4)
        Padding.PaddingBottom = UDim.new(0, 4)
        Padding.PaddingLeft = UDim.new(0, 4)
        Padding.PaddingRight = UDim.new(0, 4)
        Padding.Parent = Dropdown

        local count = 0

        for _, page in pairs(LayersFolder:GetChildren()) do
            if page:IsA("ScrollingFrame") then
                local tabName = GetTabName(page.LayoutOrder)

                for _, section in pairs(page:GetChildren()) do
                    if section.Name == "Section" then
                        local sectionTitleText = "Unknown"
                        local sectionRow = section:FindFirstChild("SectionRow")
                        if sectionRow and sectionRow:FindFirstChild("SectionTitle") then
                            sectionTitleText = sectionRow.SectionTitle.Text
                        end

                        local sectionAdd = section:FindFirstChild("SectionAdd")
                        if sectionAdd then
                            for _, item in pairs(sectionAdd:GetChildren()) do
                                if (item:IsA("Frame") or item:IsA("TextButton")) and item.Name ~= "SubSection" and item.Name ~= "Divider" then
                                    local titleObj = item:FindFirstChild("ToggleTitle") or item:FindFirstChild("SliderTitle") 
                                        or item:FindFirstChild("DropdownTitle") or item:FindFirstChild("InputTitle") or item:FindFirstChild("ParagraphTitle")

                                    if not titleObj then
                                        for _, v in pairs(item:GetChildren()) do
                                            if v:IsA("TextLabel") and v.Text ~= "" then
                                                titleObj = v
                                                break
                                            elseif v:IsA("TextButton") and v.Text ~= "" then
                                                titleObj = v
                                                break
                                            end
                                        end
                                    end

                                    if titleObj and string.find(string.lower(titleObj.Text), query, 1, true) then
                                        count += 1

                                        local ItemBtn = Instance.new("TextButton")
                                        ItemBtn.Size = UDim2.new(1, 0, 0, 36) -- Box lebih slim
                                        ItemBtn.BackgroundColor3 = Color3.fromRGB(30, 30, 35)
                                        ItemBtn.BackgroundTransparency = 0.6
                                        ItemBtn.Text = ""
                                        ItemBtn.AutoButtonColor = false
                                        ItemBtn.ZIndex = 151
                                        ItemBtn.Parent = Dropdown

                                        Instance.new("UICorner", ItemBtn).CornerRadius = UDim.new(0, 6)

                                        local Accent = Instance.new("Frame")
                                        Accent.BackgroundColor3 = GuiConfig.Color
                                        Accent.BorderSizePixel = 0
                                        Accent.Size = UDim2.new(0, 2, 0, 20)
                                        Accent.AnchorPoint = Vector2.new(0, 0.5)
                                        Accent.Position = UDim2.new(0, 6, 0.5, 0)
                                        Accent.ZIndex = 152
                                        Accent.Parent = ItemBtn
                                        Instance.new("UICorner", Accent).CornerRadius = UDim.new(1, 0)

                                        local ItemTitle = Instance.new("TextLabel")
                                        ItemTitle.Text = titleObj.Text
                                        ItemTitle.Font = Enum.Font.GothamBold
                                        ItemTitle.TextSize = 11 -- Tulisan lebih kecil
                                        ItemTitle.TextColor3 = Color3.fromRGB(255, 255, 255)
                                        ItemTitle.BackgroundTransparency = 1
                                        ItemTitle.Position = UDim2.new(0, 14, 0, 4)
                                        ItemTitle.Size = UDim2.new(1, -20, 0, 14)
                                        ItemTitle.TextXAlignment = Enum.TextXAlignment.Left
                                        ItemTitle.ZIndex = 152
                                        ItemTitle.Parent = ItemBtn

                                        local SubTitle = Instance.new("TextLabel")
                                        SubTitle.Text = tabName .. " â€¢ " .. sectionTitleText
                                        SubTitle.Font = Enum.Font.Gotham
                                        SubTitle.TextSize = 9 -- Subtext lebih kecil
                                        SubTitle.TextColor3 = Color3.fromRGB(130, 130, 130)
                                        SubTitle.BackgroundTransparency = 1
                                        SubTitle.Position = UDim2.new(0, 14, 0, 20)
                                        SubTitle.Size = UDim2.new(1, -20, 0, 12)
                                        SubTitle.TextXAlignment = Enum.TextXAlignment.Left
                                        SubTitle.ZIndex = 152
                                        SubTitle.Parent = ItemBtn

                                        ItemBtn.MouseEnter:Connect(function()
                                            TweenService:Create(ItemBtn, TweenInfo.new(0.2), { BackgroundTransparency = 0.2 }):Play()
                                        end)
                                        ItemBtn.MouseLeave:Connect(function()
                                            TweenService:Create(ItemBtn, TweenInfo.new(0.2), { BackgroundTransparency = 0.6 }):Play()
                                        end)

                                        ItemBtn.MouseButton1Click:Connect(function()
                                            LayersPageLayout:JumpToIndex(page.LayoutOrder)
                                            
                                            local FrameChoose
                                            for _, TabFrame in pairs(ScrollTab:GetChildren()) do
                                                if TabFrame.Name == "Tab" then
                                                    if TabFrame.LayoutOrder == page.LayoutOrder then
                                                        TweenService:Create(TabFrame, TweenInfo.new(0.3, Enum.EasingStyle.Back, Enum.EasingDirection.InOut), { BackgroundTransparency = 0.92 }):Play()
                                                        if not FrameChoose then
                                                            for _, t in pairs(ScrollTab:GetChildren()) do
                                                                if t:IsA("Frame") and t:FindFirstChild("ChooseFrame") then
                                                                    FrameChoose = t.ChooseFrame
                                                                end
                                                            end
                                                        end
                                                        if FrameChoose then
                                                            FrameChoose.Parent = TabFrame
                                                            FrameChoose.Position = UDim2.new(0, 0, 0, 5)
                                                            FrameChoose.Size = UDim2.new(0, 3, 0, 20)
                                                        end
                                                    else
                                                        TweenService:Create(TabFrame, TweenInfo.new(0.3, Enum.EasingStyle.Back, Enum.EasingDirection.InOut), { BackgroundTransparency = 0.999 }):Play()
                                                    end
                                                end
                                            end

                                            local contentH = 0
                                            for _, v in pairs(sectionAdd:GetChildren()) do
                                                if v.Name ~= "UIListLayout" then contentH += v.Size.Y.Offset + 3 end
                                            end
                                            
                                            local sr = section:FindFirstChild("SectionRow")
                                            if sr then
                                                local arrow = sr:FindFirstChild("ArrowImg")
                                                local st = sr:FindFirstChild("SectionTitle")
                                                if arrow then arrow.Rotation = 0 end
                                                if st then st.TextColor3 = GuiConfig.Color end
                                            end

                                            section.Size = UDim2.new(1, 0, 0, 32 + contentH)
                                            if sectionAdd then sectionAdd.Size = UDim2.new(1, 0, 0, contentH) end

                                            Dropdown:Destroy()
                                            SearchInput.Text = ""
                                            task.wait(0.1)

                                            local targetY = item.AbsolutePosition.Y - page.AbsolutePosition.Y + page.CanvasPosition.Y - 80
                                            targetY = math.max(0, targetY)
                                            TweenService:Create(
                                                page, 
                                                TweenInfo.new(0.4, Enum.EasingStyle.Cubic, Enum.EasingDirection.Out), 
                                                { CanvasPosition = Vector2.new(0, targetY) }
                                            ):Play()

                                            local HighlightStroke = Instance.new("UIStroke")
                                            HighlightStroke.Color = GuiConfig.Color
                                            HighlightStroke.Thickness = 1.5
                                            HighlightStroke.Transparency = 1
                                            HighlightStroke.ApplyStrokeMode = Enum.ApplyStrokeMode.Border
                                            HighlightStroke.Parent = item

                                            if not item:FindFirstChildOfClass("UICorner") then
                                                local tempCorner = Instance.new("UICorner")
                                                tempCorner.CornerRadius = UDim.new(0, 4)
                                                tempCorner.Parent = item
                                            end

                                            TweenService:Create(HighlightStroke, TweenInfo.new(0.2), { Transparency = 0 }):Play()
                                            task.delay(3, function()
                                                if HighlightStroke then
                                                    local fadeOut = TweenService:Create(HighlightStroke, TweenInfo.new(0.5), { Transparency = 1 })
                                                    fadeOut:Play()
                                                    fadeOut.Completed:Connect(function()
                                                        HighlightStroke:Destroy()
                                                    end)
                                                end
                                            end)
                                            
                                            local origColor = item.BackgroundColor3
                                            item.BackgroundColor3 = GuiConfig.Color
                                            task.delay(1.5, function()
                                                if item then TweenService:Create(item, TweenInfo.new(0.5), {BackgroundColor3 = origColor}):Play() end
                                            end)

                                        end)
                                    end
                                end
                            end
                        end
                    end
                end
            end
        end

        if count > 0 then
            local elementHeight = 36
            local padding = 2
            local totalContentHeight = (count * elementHeight) + ((count - 1) * padding) + 8
            
            Dropdown.CanvasSize = UDim2.new(0, 0, 0, totalContentHeight)
            Dropdown.Size = UDim2.new(1, -8, 0, math.min(totalContentHeight, 220))
        else
            Dropdown:Destroy()
        end
    end)

    -- Geser ScrollTab ke bawah biar ga ketutup
    ScrollTab.Position = UDim2.new(0, 0, 0, 32)
    ScrollTab.Size = UDim2.new(1, 0, 1, -82)

    ScrollTab.CanvasSize = UDim2.new(0, 0, 1.10000002, 0)
    ScrollTab.ScrollBarImageColor3 = Color3.fromRGB(0, 0, 0)
    ScrollTab.ScrollBarThickness = 0
    ScrollTab.Active = true
    ScrollTab.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    ScrollTab.BackgroundTransparency = 0.9990000128746033
    ScrollTab.BorderColor3 = Color3.fromRGB(0, 0, 0)
    ScrollTab.BorderSizePixel = 0
    ScrollTab.Size = UDim2.new(1, 0, 1, -50)
    ScrollTab.Name = "ScrollTab"
    ScrollTab.Parent = LayersTab

    UIListLayout.Padding = UDim.new(0, 2)
    UIListLayout.SortOrder = Enum.SortOrder.LayoutOrder
    UIListLayout.Parent = ScrollTab

    local function UpdateSize1()
        local OffsetY = 0
        for _, child in ScrollTab:GetChildren() do
            if child.Name ~= "UIListLayout" then
                OffsetY = OffsetY + 3 + child.Size.Y.Offset
            end
        end
        ScrollTab.CanvasSize = UDim2.new(0, 0, 0, OffsetY)
    end
    ScrollTab.ChildAdded:Connect(UpdateSize1)
    ScrollTab.ChildRemoved:Connect(UpdateSize1)

    function GuiFunc:DestroyGui()
        if CoreGui:FindFirstChild("MengHubGui") then
            Menghubb:Destroy()
        end

        if CoreGui:FindFirstChild("MengHubKeybindList") then
            CoreGui.MengHubKeybindList:Destroy()
        end
    end

    Min.Activated:Connect(function()
        CircleClick(Min, Mouse.X, Mouse.Y)
        DropShadowHolder.Visible = false
    end)
    Close.Activated:Connect(function()
        CircleClick(Close, Mouse.X, Mouse.Y)

        local Overlay = Instance.new("Frame")
        Overlay.Size = UDim2.new(1, 0, 1, 0)
        Overlay.BackgroundColor3 = Color3.fromRGB(0, 0, 0)
        Overlay.BackgroundTransparency = 0.3
        Overlay.ZIndex = 50
        Overlay.Parent = DropShadowHolder

        local Dialog = Instance.new("Frame")
        Dialog.Size = UDim2.new(0, 300, 0, 150)
        Dialog.Position = UDim2.new(0.5, -150, 0.5, -75)
        Dialog.BackgroundColor3 = Color3.fromRGB(30, 30, 30)
        Dialog.BorderSizePixel = 0
        Dialog.ZIndex = 51
        Dialog.Parent = Overlay
        local UICorner = Instance.new("UICorner", Dialog)
        UICorner.CornerRadius = UDim.new(0, 8)

        local DialogGlow = Instance.new("Frame")
        DialogGlow.Size = UDim2.new(0, 310, 0, 160)
        DialogGlow.Position = UDim2.new(0.5, -155, 0.5, -80)
        DialogGlow.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
        DialogGlow.BackgroundTransparency = 0.75
        DialogGlow.BorderSizePixel = 0
        DialogGlow.ZIndex = 50
        DialogGlow.Parent = Overlay

        local GlowCorner = Instance.new("UICorner", DialogGlow)
        GlowCorner.CornerRadius = UDim.new(0, 10)

        local Gradient = Instance.new("UIGradient")
        Gradient.Color = ColorSequence.new({
            ColorSequenceKeypoint.new(0.0, Color3.fromRGB(0, 191, 255)),
            ColorSequenceKeypoint.new(0.25, Color3.fromRGB(255, 255, 255)),
            ColorSequenceKeypoint.new(0.5, Color3.fromRGB(0, 140, 255)),
            ColorSequenceKeypoint.new(0.75, Color3.fromRGB(255, 255, 255)),
            ColorSequenceKeypoint.new(1.0, Color3.fromRGB(0, 191, 255))
        })
        Gradient.Rotation = 90
        Gradient.Parent = DialogGlow

        local Title = Instance.new("TextLabel")
        Title.Size = UDim2.new(1, 0, 0, 40)
        Title.Position = UDim2.new(0, 0, 0, 4)
        Title.BackgroundTransparency = 1
        Title.Font = Enum.Font.GothamBold
        Title.Text = "Meng Hub Window"
        Title.TextSize = 22
        Title.TextColor3 = Color3.fromRGB(255, 255, 255)
        Title.ZIndex = 52
        Title.Parent = Dialog

        local Message = Instance.new("TextLabel")
        Message.Size = UDim2.new(1, -20, 0, 60)
        Message.Position = UDim2.new(0, 10, 0, 30)
        Message.BackgroundTransparency = 1
        Message.Font = Enum.Font.Gotham
        Message.Text = "Do you want to close this window?\nYou will not be able to open it again"
        Message.TextSize = 14
        Message.TextColor3 = Color3.fromRGB(200, 200, 200)
        Message.TextWrapped = true
        Message.ZIndex = 52
        Message.Parent = Dialog

        local Yes = Instance.new("TextButton")
        Yes.Size = UDim2.new(0.45, -10, 0, 35)
        Yes.Position = UDim2.new(0.05, 0, 1, -55)
        Yes.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
        Yes.BackgroundTransparency = 0.935
        Yes.Text = "Yes"
        Yes.Font = Enum.Font.GothamBold
        Yes.TextSize = 15
        Yes.TextColor3 = Color3.fromRGB(255, 255, 255)
        Yes.TextTransparency = 0.3
        Yes.ZIndex = 52
        Yes.Name = "Yes"
        Yes.Parent = Dialog
        Instance.new("UICorner", Yes).CornerRadius = UDim.new(0, 6)

        local Cancel = Instance.new("TextButton")
        Cancel.Size = UDim2.new(0.45, -10, 0, 35)
        Cancel.Position = UDim2.new(0.5, 10, 1, -55)
        Cancel.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
        Cancel.BackgroundTransparency = 0.935
        Cancel.Text = "Cancel"
        Cancel.Font = Enum.Font.GothamBold
        Cancel.TextSize = 15
        Cancel.TextColor3 = Color3.fromRGB(255, 255, 255)
        Cancel.TextTransparency = 0.3
        Cancel.ZIndex = 52
        Cancel.Name = "Cancel"
        Cancel.Parent = Dialog
        Instance.new("UICorner", Cancel).CornerRadius = UDim.new(0, 6)

        Yes.MouseButton1Click:Connect(function()
            ConfigData = { _version = CURRENT_VERSION }
            if LoadConfigElements then
                LoadConfigElements()
            end

            HidePremiumTooltip()
            if CoreGui:FindFirstChild("MenghubPremiumTooltip") then
                CoreGui.MenghubPremiumTooltip:Destroy()
            end

            if Menghubb then Menghubb:Destroy() end
            if game.CoreGui:FindFirstChild("ToggleUIButton") then
                game.CoreGui.ToggleUIButton:Destroy()
            end

            if game.CoreGui:FindFirstChild("MobPanel") then
                game.CoreGui.MobPanel:Destroy()
            end
            
            for _, gui in pairs(game.CoreGui:GetChildren()) do
                if gui.Name:sub(1, 9) == "MobLabel_" then
                    gui:Destroy()
                end
            end

            getgenv().ShowRole = false
            if getgenv().RoleConnection then
                getgenv().RoleConnection:Disconnect()
                getgenv().RoleConnection = nil
            end
            getgenv().MengHubLoaded = false
            getgenv().SCRIPT_KEY = "YOUR_KEY_SYSTEM"
        end)

        Cancel.MouseButton1Click:Connect(function()
            Overlay:Destroy()
        end)

        -- getgenv().AntiAnalyticActive = false
    end)

    local ToggleKey = Enum.KeyCode.F3
    UserInputService.InputBegan:Connect(function(input, gpe)
        if gpe then return end
        
        if input.UserInputType == Enum.UserInputType.Keyboard and ToggleKey ~= Enum.KeyCode.None then
            if input.KeyCode == ToggleKey then
                if DropShadowHolder then
                    DropShadowHolder.Visible = not DropShadowHolder.Visible
                end
            end
        end
    end)

    function GuiFunc:ToggleUI()
        local UserInputService = game:GetService("UserInputService")
        local CoreGui = game:GetService("CoreGui")

        local ScreenGui = Instance.new("ScreenGui")
        ScreenGui.Parent = CoreGui
        ScreenGui.ZIndexBehavior = Enum.ZIndexBehavior.Sibling
        ScreenGui.Name = "ToggleUIButton"

        local MainButton = Instance.new("ImageLabel")
        MainButton.Parent = ScreenGui
        MainButton.Size = UDim2.new(0, 40, 0, 40)
        MainButton.Position = UDim2.new(0, 20, 0, 100)
        MainButton.BackgroundTransparency = 1
        MainButton.Image = "rbxassetid://" .. GuiConfig.Image
        MainButton.ScaleType = Enum.ScaleType.Fit
        MainButton.Active = true

        local UICorner = Instance.new("UICorner")
        UICorner.CornerRadius = UDim.new(1, 0)
        UICorner.Parent = MainButton

        local Button = Instance.new("TextButton")
        Button.Parent = MainButton
        Button.Size = UDim2.new(1, 0, 1, 0)
        Button.BackgroundTransparency = 1
        Button.Text = ""
        Button.Active = true

        local dragging = false
        local dragInput = nil
        local dragStart = nil
        local startPos = nil
        local hasDragged = false

        Button.InputBegan:Connect(function(input)
            if (input.UserInputType == Enum.UserInputType.MouseButton1 or input.UserInputType == Enum.UserInputType.Touch) then
                if input.UserInputState == Enum.UserInputState.Begin then
                    if dragging then return end 

                    dragging = true
                    hasDragged = false
                    dragInput = input
                    dragStart = input.Position
                    startPos = MainButton.Position

                    local connection
                    connection = input.Changed:Connect(function()
                        if input.UserInputState == Enum.UserInputState.End then
                            dragging = false
                            dragInput = nil
                            if connection then 
                                connection:Disconnect() 
                            end
                        end
                    end)
                end
            end
        end)

        UserInputService.InputChanged:Connect(function(input)
            if dragging and dragInput then
                local isTouch = (input == dragInput)
                local isMouse = (dragInput.UserInputType == Enum.UserInputType.MouseButton1 and input.UserInputType == Enum.UserInputType.MouseMovement)

                if isTouch or isMouse then
                    local delta = input.Position - dragStart
                    
                    if delta.Magnitude > 5 then
                        hasDragged = true
                    end

                    MainButton.Position = UDim2.new(
                        startPos.X.Scale,
                        startPos.X.Offset + delta.X,
                        startPos.Y.Scale,
                        startPos.Y.Offset + delta.Y
                    )
                end
            end
        end)

        Button.MouseButton1Click:Connect(function()
            if hasDragged then return end
            
            if DropShadowHolder then
                DropShadowHolder.Visible = not DropShadowHolder.Visible
            end
        end)
    end
    GuiFunc:ToggleUI()

    DropShadowHolder.Size = UDim2.new(0, 115 + TextLabel.TextBounds.X + 1 + TextLabel1.TextBounds.X, 0, 350)
    MakeDraggable(Top, DropShadowHolder)

    local KeybindEntries = {}
    local KeybindListVisible = true

    local KeybindListGui = Instance.new("ScreenGui")
    KeybindListGui.Name = "MengHubKeybindList"
    KeybindListGui.ResetOnSpawn = false
    KeybindListGui.IgnoreGuiInset = true
    KeybindListGui.ZIndexBehavior = Enum.ZIndexBehavior.Sibling
    KeybindListGui.DisplayOrder = 50
    KeybindListGui.Parent = CoreGui

    local KeybindListFrame = Instance.new("Frame")
    KeybindListFrame.Name = "KeybindListFrame"
    KeybindListFrame.AnchorPoint = Vector2.new(0, 0)
    KeybindListFrame.Position = UDim2.new(0, 14, 0, 70)
    KeybindListFrame.Size = UDim2.new(0, 0, 0, 0)
    KeybindListFrame.AutomaticSize = Enum.AutomaticSize.XY
    KeybindListFrame.BackgroundTransparency = 1
    KeybindListFrame.BorderSizePixel = 0
    KeybindListFrame.Visible = true
    KeybindListFrame.Parent = KeybindListGui

    local KeybindListLayout = Instance.new("UIListLayout")
    KeybindListLayout.FillDirection = Enum.FillDirection.Vertical
    KeybindListLayout.HorizontalAlignment = Enum.HorizontalAlignment.Left
    KeybindListLayout.VerticalAlignment = Enum.VerticalAlignment.Top
    KeybindListLayout.Padding = UDim.new(0, 3)
    KeybindListLayout.SortOrder = Enum.SortOrder.LayoutOrder
    KeybindListLayout.Parent = KeybindListFrame

    local function RefreshKeybindList()
        for _, child in pairs(KeybindListFrame:GetChildren()) do
            if child:IsA("Frame") and child.Name == "KeybindEntry" then
                child:Destroy()
            end
        end

        if not KeybindListVisible then
            KeybindListFrame.Visible = false
            return
        end

        local hasAny = false
        local order = 0

        for key, data in pairs(KeybindEntries) do
            local currentKey = data.GetKey and data.GetKey() or "None"
            if currentKey and currentKey ~= "None" and currentKey ~= "" then
                hasAny = true
                order = order + 1

                local keyStr = "[" .. tostring(currentKey) .. "]"
                local titleStr = data.Title or "Unknown"

                local Entry = Instance.new("Frame")
                Entry.Name = "KeybindEntry"
                Entry.BackgroundColor3 = Color3.fromRGB(12, 12, 16)
                Entry.BackgroundTransparency = 0.3
                Entry.BorderSizePixel = 0
                Entry.AutomaticSize = Enum.AutomaticSize.X
                Entry.Size = UDim2.new(0, 0, 0, 22)
                Entry.LayoutOrder = order
                Entry.ClipsDescendants = false
                Entry.Parent = KeybindListFrame

                Instance.new("UICorner", Entry).CornerRadius = UDim.new(0, 4)

                local Grad = Instance.new("UIGradient")
                Grad.Transparency = NumberSequence.new({
                    NumberSequenceKeypoint.new(0, 0.1),
                    NumberSequenceKeypoint.new(0.7, 0.35),
                    NumberSequenceKeypoint.new(1, 0.9)
                })
                Grad.Parent = Entry

                local Content = Instance.new("Frame")
                Content.Name = "Content"
                Content.BackgroundTransparency = 1
                Content.AutomaticSize = Enum.AutomaticSize.X
                Content.Size = UDim2.new(0, 0, 1, 0)
                Content.Parent = Entry

                local ContentPadding = Instance.new("UIPadding")
                ContentPadding.PaddingLeft = UDim.new(0, 8)
                ContentPadding.PaddingRight = UDim.new(0, 10)
                ContentPadding.Parent = Content

                local ContentLayout = Instance.new("UIListLayout")
                ContentLayout.FillDirection = Enum.FillDirection.Horizontal
                ContentLayout.VerticalAlignment = Enum.VerticalAlignment.Center
                ContentLayout.HorizontalAlignment = Enum.HorizontalAlignment.Left
                ContentLayout.Padding = UDim.new(0, 4)
                ContentLayout.Parent = Content

                local KeyLbl = Instance.new("TextLabel")
                KeyLbl.BackgroundTransparency = 1
                KeyLbl.Font = Enum.Font.GothamBold
                KeyLbl.Text = keyStr
                KeyLbl.TextColor3 = GuiConfig.Color
                KeyLbl.TextSize = 13
                KeyLbl.AutomaticSize = Enum.AutomaticSize.X
                KeyLbl.Size = UDim2.new(0, 0, 1, 0)
                KeyLbl.LayoutOrder = 0
                KeyLbl.Parent = Content

                local TitleLbl = Instance.new("TextLabel")
                TitleLbl.BackgroundTransparency = 1
                TitleLbl.Font = Enum.Font.Gotham
                TitleLbl.Text = titleStr
                TitleLbl.TextColor3 = Color3.fromRGB(255, 255, 255)
                TitleLbl.TextSize = 13
                TitleLbl.AutomaticSize = Enum.AutomaticSize.X
                TitleLbl.Size = UDim2.new(0, 0, 1, 0)
                TitleLbl.LayoutOrder = 1
                TitleLbl.Parent = Content

                local AccentLine = Instance.new("Frame")
                AccentLine.Name = "AccentLine"
                AccentLine.BackgroundColor3 = GuiConfig.Color
                AccentLine.BorderSizePixel = 0
                AccentLine.Size = UDim2.new(0, 2, 0, 14)
                AccentLine.AnchorPoint = Vector2.new(0, 0.5)
                AccentLine.Position = UDim2.new(0, 0, 0.5, 0)
                AccentLine.ZIndex = 5
                AccentLine.Parent = Entry
                Instance.new("UICorner", AccentLine).CornerRadius = UDim.new(1, 0)
            end
        end

        KeybindListFrame.Visible = hasAny
    end

    local function RegisterKeybindEntry(keybindKey, title, getKeyFunc)
        KeybindEntries[keybindKey] = {
            Title = title,
            GetKey = getKeyFunc
        }
        RefreshKeybindList()
    end

    local function UpdateKeybindEntry(keybindKey, newKey)
        if KeybindEntries[keybindKey] then
            RefreshKeybindList()
        end
    end

    local MoreBlur = Instance.new("Frame");
    local DropShadowHolder1 = Instance.new("Frame");
    local DropShadow1 = Instance.new("ImageLabel");
    local UICorner28 = Instance.new("UICorner");
    local ConnectButton = Instance.new("TextButton");

    MoreBlur.AnchorPoint = Vector2.new(1, 1)
    MoreBlur.BackgroundColor3 = Color3.fromRGB(0, 0, 0)
    MoreBlur.BackgroundTransparency = 0.999
    MoreBlur.BorderColor3 = Color3.fromRGB(0, 0, 0)
    MoreBlur.BorderSizePixel = 0
    MoreBlur.ClipsDescendants = true
    MoreBlur.Position = UDim2.new(1, 8, 1, 8)
    MoreBlur.Size = UDim2.new(1, 154, 1, 54)
    MoreBlur.Visible = false
    MoreBlur.Name = "MoreBlur"
    MoreBlur.Parent = Layers

    DropShadowHolder1.BackgroundTransparency = 1
    DropShadowHolder1.BorderSizePixel = 0
    DropShadowHolder1.Size = UDim2.new(1, 0, 1, 0)
    DropShadowHolder1.ZIndex = 0
    DropShadowHolder1.Name = "DropShadowHolder"
    DropShadowHolder1.Parent = MoreBlur

    DropShadow1.Image = "rbxassetid://6015897843"
    DropShadow1.ImageColor3 = Color3.fromRGB(0, 0, 0)
    DropShadow1.ImageTransparency = 1
    DropShadow1.ScaleType = Enum.ScaleType.Slice
    DropShadow1.SliceCenter = Rect.new(49, 49, 450, 450)
    DropShadow1.AnchorPoint = Vector2.new(0.5, 0.5)
    DropShadow1.BackgroundTransparency = 1
    DropShadow1.BorderSizePixel = 0
    DropShadow1.Position = UDim2.new(0.5, 0, 0.5, 0)
    DropShadow1.Size = UDim2.new(1, 35, 1, 35)
    DropShadow1.ZIndex = 0
    DropShadow1.Name = "DropShadow"
    DropShadow1.Parent = DropShadowHolder1

    UICorner28.Parent = MoreBlur

    ConnectButton.Font = Enum.Font.SourceSans
    ConnectButton.Text = ""
    ConnectButton.TextColor3 = Color3.fromRGB(0, 0, 0)
    ConnectButton.TextSize = 14
    ConnectButton.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
    ConnectButton.BackgroundTransparency = 0.999
    ConnectButton.BorderColor3 = Color3.fromRGB(0, 0, 0)
    ConnectButton.BorderSizePixel = 0
    ConnectButton.Size = UDim2.new(1, 0, 1, 0)
    ConnectButton.Name = "ConnectButton"
    ConnectButton.Parent = MoreBlur

    local DropdownSelect = Instance.new("Frame");
    local UICorner36 = Instance.new("UICorner");
    local UIStroke14 = Instance.new("UIStroke");
    local DropdownSelectReal = Instance.new("Frame");
    local DropdownFolder = Instance.new("Folder");
    local DropPageLayout = Instance.new("UIPageLayout");

    DropdownSelect.AnchorPoint = Vector2.new(1, 0.5)
    DropdownSelect.BackgroundColor3 = Color3.fromRGB(30.00000011175871, 30.00000011175871, 30.00000011175871)
    DropdownSelect.BorderColor3 = Color3.fromRGB(0, 0, 0)
    DropdownSelect.BorderSizePixel = 0
    DropdownSelect.LayoutOrder = 1
    DropdownSelect.Position = UDim2.new(1, 172, 0.5, 0)
    DropdownSelect.Size = UDim2.new(0, 160, 1, -16)
    DropdownSelect.Name = "DropdownSelect"
    DropdownSelect.ClipsDescendants = true
    DropdownSelect.Parent = MoreBlur

    ConnectButton.Activated:Connect(function()
        if MoreBlur.Visible then
            TweenService:Create(MoreBlur, TweenInfo.new(0.3), { BackgroundTransparency = 0.999 }):Play()
            TweenService:Create(DropdownSelect, TweenInfo.new(0.3), { Position = UDim2.new(1, 172, 0.5, 0) }):Play()
            task.wait(0.3)
            MoreBlur.Visible = false
        end
    end)
    UICorner36.CornerRadius = UDim.new(0, 3)
    UICorner36.Parent = DropdownSelect

    UIStroke14.Color = Color3.fromRGB(189, 162, 241)
    UIStroke14.Thickness = 2.5
    UIStroke14.Transparency = 0.8
    UIStroke14.Parent = DropdownSelect

    DropdownSelectReal.AnchorPoint = Vector2.new(0.5, 0.5)
    DropdownSelectReal.BackgroundColor3 = Color3.fromRGB(30, 30, 30)
    DropdownSelectReal.BackgroundTransparency = 0.7
    DropdownSelectReal.BorderColor3 = Color3.fromRGB(0, 0, 0)
    DropdownSelectReal.BorderSizePixel = 0
    DropdownSelectReal.LayoutOrder = 1
    DropdownSelectReal.Position = UDim2.new(0.5, 0, 0.5, 0)
    DropdownSelectReal.Size = UDim2.new(1, 1, 1, 1)
    DropdownSelectReal.Name = "DropdownSelectReal"
    DropdownSelectReal.Parent = DropdownSelect

    DropdownFolder.Name = "DropdownFolder"
    DropdownFolder.Parent = DropdownSelectReal

    DropPageLayout.EasingDirection = Enum.EasingDirection.InOut
    DropPageLayout.EasingStyle = Enum.EasingStyle.Quad
    DropPageLayout.TweenTime = 0.009999999776482582
    DropPageLayout.SortOrder = Enum.SortOrder.LayoutOrder
    DropPageLayout.FillDirection = Enum.FillDirection.Vertical
    DropPageLayout.Archivable = false
    DropPageLayout.Name = "DropPageLayout"
    DropPageLayout.Parent = DropdownFolder

    --// Tabs
    local Tabs = {}
    local CountTab = 0
    local CountDropdown = 0
    function Tabs:AddTab(TabConfig)
        local TabConfig = TabConfig or {}
        TabConfig.Name = TabConfig.Name or "Tab"
        TabConfig.Icon = TabConfig.Icon or ""

        local ScrolLayers = Instance.new("ScrollingFrame");
        local UIListLayout1 = Instance.new("UIListLayout");

        ScrolLayers.ScrollBarImageColor3 = Color3.fromRGB(80.00000283122063, 80.00000283122063, 80.00000283122063)
        ScrolLayers.ScrollBarThickness = 0
        ScrolLayers.Active = true
        ScrolLayers.LayoutOrder = CountTab
        ScrolLayers.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
        ScrolLayers.BackgroundTransparency = 0.9990000128746033
        ScrolLayers.BorderColor3 = Color3.fromRGB(0, 0, 0)
        ScrolLayers.BorderSizePixel = 0
        ScrolLayers.Size = UDim2.new(1, 0, 1, 0)
        ScrolLayers.Name = "ScrolLayers"
        ScrolLayers.Parent = LayersFolder

        UIListLayout1.Padding = UDim.new(0, 3)
        UIListLayout1.SortOrder = Enum.SortOrder.LayoutOrder
        UIListLayout1.Parent = ScrolLayers

        local Tab = Instance.new("Frame");
        local UICorner3 = Instance.new("UICorner");
        local TabButton = Instance.new("TextButton");
        local TabName = Instance.new("TextLabel")
        local FeatureImg = Instance.new("ImageLabel");
        local UIStroke2 = Instance.new("UIStroke");
        local UICorner4 = Instance.new("UICorner");

        Tab.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
        if CountTab == 0 then
            Tab.BackgroundTransparency = 0.9200000166893005
        else
            Tab.BackgroundTransparency = 0.9990000128746033
        end
        Tab.BorderColor3 = Color3.fromRGB(0, 0, 0)
        Tab.BorderSizePixel = 0
        Tab.LayoutOrder = CountTab
        Tab.Size = UDim2.new(1, 0, 0, 28)
        Tab.Name = "Tab"
        Tab.Parent = ScrollTab

        UICorner3.CornerRadius = UDim.new(0, 4)
        UICorner3.Parent = Tab

        TabButton.Font = Enum.Font.GothamBold
        TabButton.Text = ""
        TabButton.TextColor3 = Color3.fromRGB(255, 255, 255)
        TabButton.TextSize = 13
        TabButton.TextXAlignment = Enum.TextXAlignment.Left
        TabButton.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
        TabButton.BackgroundTransparency = 0.9990000128746033
        TabButton.BorderColor3 = Color3.fromRGB(0, 0, 0)
        TabButton.BorderSizePixel = 0
        TabButton.Size = UDim2.new(1, 0, 1, 0)
        TabButton.Name = "TabButton"
        TabButton.Parent = Tab

        TabName.Font = Enum.Font.GothamBold
        TabName.Text = tostring(TabConfig.Name)
        TabName.TextColor3 = Color3.fromRGB(255, 255, 255)
        TabName.TextSize = 13
        TabName.TextXAlignment = Enum.TextXAlignment.Left
        TabName.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
        TabName.BackgroundTransparency = 0.9990000128746033
        TabName.BorderColor3 = Color3.fromRGB(0, 0, 0)
        TabName.BorderSizePixel = 0
        TabName.Size = UDim2.new(1, 0, 1, 0)
        TabName.Position = UDim2.new(0, 10, 0, 0)
        TabName.Name = "TabName"
        TabName.Parent = Tab

        FeatureImg.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
        FeatureImg.BackgroundTransparency = 0.9990000128746033
        FeatureImg.BorderColor3 = Color3.fromRGB(0, 0, 0)
        FeatureImg.BorderSizePixel = 0
        FeatureImg.Position = UDim2.new(0, 9, 0, 7)
        FeatureImg.Size = UDim2.new(0, 16, 0, 16)
        FeatureImg.Name = "FeatureImg"
        FeatureImg.Parent = Tab
        if TabConfig.Icon ~= "" then
            FeatureImg.Visible = true
            TabName.Position = UDim2.new(0, 32, 0, 0)
            if Icons[TabConfig.Icon] then
                FeatureImg.Image = Icons[TabConfig.Icon]
            else
                FeatureImg.Image = TabConfig.Icon
            end
        else
            FeatureImg.Visible = false
        end
        if CountTab == 0 then
            LayersPageLayout:JumpToIndex(0)
            local ChooseFrame = Instance.new("Frame");
            ChooseFrame.BackgroundColor3 = GuiConfig.Color
            ChooseFrame.BorderColor3 = Color3.fromRGB(0, 0, 0)
            ChooseFrame.BorderSizePixel = 0
            ChooseFrame.Position = UDim2.new(0, 0, 0, 5)
            ChooseFrame.Size = UDim2.new(0, 3, 0, 20)
            ChooseFrame.Name = "ChooseFrame"
            ChooseFrame.Parent = Tab

            UIStroke2.Color = GuiConfig.Color
            UIStroke2.Thickness = 1.600000023841858
            UIStroke2.Parent = ChooseFrame

            UICorner4.Parent = ChooseFrame
        end

        TabButton.Activated:Connect(function()
            CircleClick(TabButton, Mouse.X, Mouse.Y)
            local FrameChoose
            for a, s in ScrollTab:GetChildren() do
                for i, v in s:GetChildren() do
                    if v.Name == "ChooseFrame" then
                        FrameChoose = v
                        break
                    end
                end
            end
            if FrameChoose ~= nil and Tab.LayoutOrder ~= LayersPageLayout.CurrentPage.LayoutOrder then
                -- reset tab highlights
                for _, TabFrame in ScrollTab:GetChildren() do
                    if TabFrame.Name == "Tab" then
                        TweenService:Create(
                            TabFrame,
                            TweenInfo.new(0.3, Enum.EasingStyle.Back, Enum.EasingDirection.InOut),
                            { BackgroundTransparency = 0.9990000128746033 }
                        ):Play()
                    end
                end
                TweenService:Create(
                    Tab,
                    TweenInfo.new(0.6, Enum.EasingStyle.Back, Enum.EasingDirection.InOut),
                    { BackgroundTransparency = 0.9200000166893005 }
                ):Play()

                FrameChoose.Parent = Tab
                FrameChoose.Position = UDim2.new(0, 0, 0, 5)
                FrameChoose.Size = UDim2.new(0, 3, 0, 20)

                -- flash overlay masuk cepet
                TweenService:Create(
                    TabSwitchOverlay,
                    TweenInfo.new(0.08, Enum.EasingStyle.Quad, Enum.EasingDirection.Out),
                    { BackgroundTransparency = 0.45 }
                ):Play()
                task.wait(0.08)

                -- instant switch
                LayersPageLayout:JumpToIndex(Tab.LayoutOrder)

                -- slide up dari bawah
                local newPage = LayersPageLayout.CurrentPage
                if newPage and newPage:IsA("ScrollingFrame") then
                    newPage.CanvasPosition = Vector2.new(0, 40)
                    TweenService:Create(
                        newPage,
                        TweenInfo.new(0.28, Enum.EasingStyle.Quint, Enum.EasingDirection.Out),
                        { CanvasPosition = Vector2.new(0, 0) }
                    ):Play()
                end

                -- overlay fade out barengan sama slide
                TweenService:Create(
                    TabSwitchOverlay,
                    TweenInfo.new(0.22, Enum.EasingStyle.Quad, Enum.EasingDirection.Out),
                    { BackgroundTransparency = 1 }
                ):Play()

                -- FrameChoose bounce
                task.wait(0.05)
                TweenService:Create(
                    FrameChoose,
                    TweenInfo.new(0.3, Enum.EasingStyle.Back, Enum.EasingDirection.Out),
                    { Size = UDim2.new(0, 3, 0, 22) }
                ):Play()
                task.wait(0.2)
                TweenService:Create(
                    FrameChoose,
                    TweenInfo.new(0.2, Enum.EasingStyle.Quad, Enum.EasingDirection.InOut),
                    { Size = UDim2.new(0, 3, 0, 18) }
                ):Play()
            end
        end)

        --// Section
        local Sections = {}
        local CountSection = 0

        function Sections:AddSection(Title, AlwaysOpen)
            Title = Title or "Title"

            local Section = Instance.new("Frame")
            Section.BackgroundTransparency = 1
            Section.BorderSizePixel = 0
            Section.LayoutOrder = CountSection
            Section.ClipsDescendants = false
            Section.Size = UDim2.new(1, 0, 0, 30)
            Section.Name = "Section"
            Section.Parent = ScrolLayers

            local SectionRow = Instance.new("Frame")
            SectionRow.Name = "SectionRow"
            SectionRow.BackgroundTransparency = 1
            SectionRow.BorderSizePixel = 0
            SectionRow.Size = UDim2.new(1, 0, 0, 30)
            SectionRow.Position = UDim2.new(0, 0, 0, 0)
            SectionRow.Parent = Section

            -- Title teks, kiri
            local SectionTitle = Instance.new("TextLabel")
            SectionTitle.Font = Enum.Font.GothamBold
            SectionTitle.Text = Title
            SectionTitle.TextColor3 = Color3.fromRGB(255, 255, 255)
            SectionTitle.TextSize = 14
            SectionTitle.TextXAlignment = Enum.TextXAlignment.Left
            SectionTitle.BackgroundTransparency = 1
            SectionTitle.Position = UDim2.new(0, 5, 0, 0)
            SectionTitle.Size = UDim2.new(1, -30, 1, 0)
            SectionTitle.Name = "SectionTitle"
            SectionTitle.Parent = SectionRow

            -- Arrow kanan (? / ?)
            local ArrowImg = Instance.new("ImageLabel")
            ArrowImg.Image = "rbxassetid://16851841101"
            ArrowImg.ImageColor3 = Color3.fromRGB(255, 255, 255)
            ArrowImg.AnchorPoint = Vector2.new(1, 0.5)
            ArrowImg.BackgroundTransparency = 1
            ArrowImg.Position = UDim2.new(1, -4, 0.5, 0)
            ArrowImg.Size = UDim2.new(0, 22, 0, 22)
            ArrowImg.Rotation = -90  -- menunjuk kanan saat closed
            ArrowImg.Name = "ArrowImg"
            ArrowImg.Parent = SectionRow

            -- Button klik transparan
            local SectionButton = Instance.new("TextButton")
            SectionButton.Text = ""
            SectionButton.BackgroundTransparency = 1
            SectionButton.Size = UDim2.new(1, 0, 1, 0)
            SectionButton.Name = "SectionButton"
            SectionButton.Parent = SectionRow

            -- SectionAdd: isi items, mulai height 0
            local SectionAdd = Instance.new("Frame")
            SectionAdd.AnchorPoint = Vector2.new(0.5, 0)
            SectionAdd.BackgroundTransparency = 1
            SectionAdd.BorderSizePixel = 0
            SectionAdd.ClipsDescendants = true
            SectionAdd.Position = UDim2.new(0.5, 0, 0, 32)
            SectionAdd.Size = UDim2.new(1, 0, 0, 0)
            SectionAdd.Name = "SectionAdd"
            SectionAdd.Parent = Section

            local UIListLayout2 = Instance.new("UIListLayout")
            UIListLayout2.Padding = UDim.new(0, 3)
            UIListLayout2.SortOrder = Enum.SortOrder.LayoutOrder
            UIListLayout2.Parent = SectionAdd

            local OpenSection = false
            local isAnimating = false
            local ANIM_TIME = 0.2
            local ANIM_STYLE = Enum.EasingStyle.Quart
            local ANIM_DIR = Enum.EasingDirection.Out

            local function UpdateSizeScroll()
                local OffsetY = 0
                for _, child in ScrolLayers:GetChildren() do
                    if child.Name ~= "UIListLayout" then
                        OffsetY = OffsetY + 3 + child.Size.Y.Offset
                    end
                end
                ScrolLayers.CanvasSize = UDim2.new(0, 0, 0, OffsetY)
            end

            local function GetContentHeight()
                local h = 0
                for _, v in SectionAdd:GetChildren() do
                    if v.Name ~= "UIListLayout" then
                        h = h + v.Size.Y.Offset + 3
                    end
                end
                return h
            end

            local function UpdateSizeSection()
                if OpenSection then
                    local contentH = GetContentHeight()
                    local totalH = 30 + 2 + contentH
                    TweenService:Create(Section, TweenInfo.new(ANIM_TIME, ANIM_STYLE, ANIM_DIR),
                        { Size = UDim2.new(1, 0, 0, totalH) }):Play()
                    TweenService:Create(SectionAdd, TweenInfo.new(ANIM_TIME, ANIM_STYLE, ANIM_DIR),
                        { Size = UDim2.new(1, 0, 0, contentH) }):Play()
                    task.delay(ANIM_TIME, UpdateSizeScroll)
                end
            end

            local function DoOpen()
                OpenSection = true
                TweenService:Create(SectionTitle, TweenInfo.new(0.15), { TextColor3 = GuiConfig.Color }):Play()
                TweenService:Create(ArrowImg, TweenInfo.new(0.18), { Rotation = 0 }):Play()
                UpdateSizeSection()
            end

            local function DoClose()
                OpenSection = false
                TweenService:Create(SectionTitle, TweenInfo.new(0.15), { TextColor3 = Color3.fromRGB(255, 255, 255) }):Play()
                TweenService:Create(ArrowImg, TweenInfo.new(0.18), { Rotation = -90 }):Play()
                TweenService:Create(Section, TweenInfo.new(ANIM_TIME, ANIM_STYLE, ANIM_DIR),
                    { Size = UDim2.new(1, 0, 0, 30) }):Play()
                TweenService:Create(SectionAdd, TweenInfo.new(ANIM_TIME, ANIM_STYLE, ANIM_DIR),
                    { Size = UDim2.new(1, 0, 0, 0) }):Play()
                task.delay(ANIM_TIME, UpdateSizeScroll)
            end

            if AlwaysOpen == true then
                DoOpen()
            end

            SectionButton.Activated:Connect(function()
                if isAnimating then return end
                isAnimating = true
                if OpenSection then
                    DoClose()
                else
                    DoOpen()
                end
                task.delay(ANIM_TIME + 0.05, function()
                    isAnimating = false
                end)
            end)

            local sizeDirty = false
            local function MarkSizeDirty()
                if sizeDirty then return end
                sizeDirty = true
                task.defer(function()
                    sizeDirty = false
                    if OpenSection then
                        UpdateSizeSection()
                    end
                end)
            end

            SectionAdd.ChildAdded:Connect(MarkSizeDirty)
            SectionAdd.ChildRemoved:Connect(MarkSizeDirty)

            local layout = ScrolLayers:FindFirstChildOfClass("UIListLayout")
            if layout then
                layout:GetPropertyChangedSignal("AbsoluteContentSize"):Connect(function()
                    ScrolLayers.CanvasSize = UDim2.new(0, 0, 0, layout.AbsoluteContentSize.Y + 10)
                end)
            end

            local Items = {}
            local CountItem = 0

            function Items:AddParagraph(ParagraphConfig)
                local ParagraphConfig = ParagraphConfig or {}
                ParagraphConfig.Title = ParagraphConfig.Title or "Title"
                ParagraphConfig.Content = ParagraphConfig.Content or "Content"
                local ParagraphFunc = {}

                local Paragraph = Instance.new("Frame")
                local UICorner14 = Instance.new("UICorner")
                local ParagraphTitle = Instance.new("TextLabel")
                local ParagraphContent = Instance.new("TextLabel")

                Paragraph.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                Paragraph.BackgroundTransparency = 0.935
                Paragraph.BorderSizePixel = 0
                Paragraph.LayoutOrder = CountItem
                Paragraph.Size = UDim2.new(1, 0, 0, 46)
                Paragraph.Name = "Paragraph"
                Paragraph.Parent = SectionAdd

                UICorner14.CornerRadius = UDim.new(0, 4)
                UICorner14.Parent = Paragraph

                local iconOffset = 10
                if ParagraphConfig.Icon then
                    local IconImg = Instance.new("ImageLabel")
                    IconImg.Size = UDim2.new(0, 20, 0, 20)
                    IconImg.Position = UDim2.new(0, 8, 0, 12)
                    IconImg.BackgroundTransparency = 1
                    IconImg.Name = "ParagraphIcon"
                    IconImg.Parent = Paragraph

                    if Icons and Icons[ParagraphConfig.Icon] then
                        IconImg.Image = Icons[ParagraphConfig.Icon]
                    else
                        IconImg.Image = ParagraphConfig.Icon
                    end

                    iconOffset = 30
                end

                ParagraphTitle.Font = Enum.Font.GothamBold
                ParagraphTitle.Text = ParagraphConfig.Title
                ParagraphTitle.TextColor3 = Color3.fromRGB(231, 231, 231)
                ParagraphTitle.TextSize = 13
                ParagraphTitle.TextXAlignment = Enum.TextXAlignment.Left
                ParagraphTitle.TextYAlignment = Enum.TextYAlignment.Top
                ParagraphTitle.BackgroundTransparency = 1
                ParagraphTitle.Position = UDim2.new(0, iconOffset, 0, 10)
                ParagraphTitle.Size = UDim2.new(1, -16, 0, 13)
                ParagraphTitle.Name = "ParagraphTitle"
                ParagraphTitle.Parent = Paragraph

                ParagraphContent.Font = Enum.Font.Gotham
                ParagraphContent.Text = ParagraphConfig.Content
                ParagraphContent.TextColor3 = Color3.fromRGB(255, 255, 255)
                ParagraphContent.TextSize = 12
                ParagraphContent.TextXAlignment = Enum.TextXAlignment.Left
                ParagraphContent.TextYAlignment = Enum.TextYAlignment.Top
                ParagraphContent.BackgroundTransparency = 1
                ParagraphContent.Position = UDim2.new(0, iconOffset, 0, 25)
                ParagraphContent.Name = "ParagraphContent"
                ParagraphContent.TextWrapped = true
                ParagraphContent.RichText = true
                ParagraphContent.Parent = Paragraph

                ParagraphContent.Size = UDim2.new(1, -16, 0, ParagraphContent.TextBounds.Y)

                local ParagraphButton
                if ParagraphConfig.ButtonText then
                    ParagraphButton = Instance.new("TextButton")
                    ParagraphButton.Position = UDim2.new(0, 10, 0, 42)
                    ParagraphButton.Size = UDim2.new(1, -22, 0, 28)
                    ParagraphButton.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                    ParagraphButton.BackgroundTransparency = 0.935
                    ParagraphButton.Font = Enum.Font.GothamBold
                    ParagraphButton.TextSize = 12
                    ParagraphButton.TextTransparency = 0.3
                    ParagraphButton.TextColor3 = Color3.fromRGB(255, 255, 255)
                    ParagraphButton.Text = ParagraphConfig.ButtonText
                    ParagraphButton.Parent = Paragraph

                    local btnCorner = Instance.new("UICorner")
                    btnCorner.CornerRadius = UDim.new(0, 6)
                    btnCorner.Parent = ParagraphButton

                    if ParagraphConfig.ButtonCallback then
                        ParagraphButton.MouseButton1Click:Connect(ParagraphConfig.ButtonCallback)
                    end
                end

                local function UpdateSize()
                    local totalHeight = ParagraphContent.TextBounds.Y + 33
                    if ParagraphButton then
                        totalHeight = totalHeight + ParagraphButton.Size.Y.Offset + 5
                    end
                    Paragraph.Size = UDim2.new(1, 0, 0, totalHeight)
                end

                UpdateSize()
                ParagraphContent:GetPropertyChangedSignal("TextBounds"):Connect(UpdateSize)

                function ParagraphFunc:SetContent(content)
                    content = content or "Content"
                    ParagraphContent.Text = content
                    UpdateSize()
                end

                function ParagraphFunc:SetIcon(icon)
                    local IconLabel = Paragraph:FindFirstChild("ParagraphIcon")
                    if IconLabel then
                        if Icons and Icons[icon] then
                            IconLabel.Image = Icons[icon]
                        else
                            IconLabel.Image = icon or ""
                        end
                    end
                end

                function ParagraphFunc:SetTitle(title)
                    title = title or "Title"
                    ParagraphTitle.Text = title
                    UpdateSize()
                end

                function ParagraphFunc:Set(title, content)
                    if title then self:SetTitle(title) end
                    if content then self:SetContent(content) end
                end

                CountItem = CountItem + 1
                return ParagraphFunc
            end

            function Items:AddPanel(PanelConfig)
                PanelConfig = PanelConfig or {}
                PanelConfig.Title = PanelConfig.Title or "Title"
                PanelConfig.Content = PanelConfig.Content or ""
                PanelConfig.Placeholder = PanelConfig.Placeholder or nil
                PanelConfig.Default = PanelConfig.Default or ""
                PanelConfig.ButtonText = PanelConfig.Button or PanelConfig.ButtonText or "Confirm"
                PanelConfig.ButtonCallback = PanelConfig.Callback or PanelConfig.ButtonCallback or function() end
                PanelConfig.SubButtonText = PanelConfig.SubButton or PanelConfig.SubButtonText or nil
                PanelConfig.SubButtonCallback = PanelConfig.SubCallback or PanelConfig.SubButtonCallback or function() end

                local configKey = "Panel_" .. PanelConfig.Title
                if ConfigData[configKey] ~= nil then
                    PanelConfig.Default = ConfigData[configKey]
                end

                local PanelFunc = { Value = PanelConfig.Default }

                local baseHeight = 50
                if PanelConfig.Placeholder then baseHeight = baseHeight + 40 end
                if PanelConfig.SubButtonText then baseHeight = baseHeight + 40 else baseHeight = baseHeight + 36 end

                local Panel = Instance.new("Frame")
                Panel.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                Panel.BackgroundTransparency = 0.935
                Panel.Size = UDim2.new(1, 0, 0, baseHeight)
                Panel.LayoutOrder = CountItem
                Panel.Parent = SectionAdd

                local UICorner = Instance.new("UICorner")
                UICorner.CornerRadius = UDim.new(0, 4)
                UICorner.Parent = Panel

                local Title = Instance.new("TextLabel")
                Title.Font = Enum.Font.GothamBold
                Title.Text = PanelConfig.Title
                Title.TextSize = 13
                Title.TextColor3 = Color3.fromRGB(255, 255, 255)
                Title.TextXAlignment = Enum.TextXAlignment.Left
                Title.BackgroundTransparency = 1
                Title.Position = UDim2.new(0, 10, 0, 10)
                Title.Size = UDim2.new(1, -20, 0, 13)
                Title.Parent = Panel

                local Content = Instance.new("TextLabel")
                Content.Font = Enum.Font.Gotham
                Content.Text = PanelConfig.Content
                Content.TextSize = 12
                Content.TextColor3 = Color3.fromRGB(255, 255, 255)
                Content.TextTransparency = 0
                Content.TextXAlignment = Enum.TextXAlignment.Left
                Content.BackgroundTransparency = 1
                Content.RichText = true
                Content.Position = UDim2.new(0, 10, 0, 28)
                Content.Size = UDim2.new(1, -20, 0, 14)
                Content.Parent = Panel

                local InputBox
                if PanelConfig.Placeholder then
                    local InputFrame = Instance.new("Frame")
                    InputFrame.AnchorPoint = Vector2.new(0.5, 0)
                    InputFrame.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                    InputFrame.BackgroundTransparency = 0.95
                    InputFrame.Position = UDim2.new(0.5, 0, 0, 48)
                    InputFrame.Size = UDim2.new(1, -20, 0, 30)
                    InputFrame.Parent = Panel
                    Instance.new("UICorner", InputFrame).CornerRadius = UDim.new(0, 4)

                    InputBox = Instance.new("TextBox")
                    InputBox.Font = Enum.Font.GothamBold
                    InputBox.PlaceholderText = PanelConfig.Placeholder
                    InputBox.PlaceholderColor3 = Color3.fromRGB(120, 120, 120)
                    InputBox.Text = PanelConfig.Default
                    InputBox.TextSize = 11
                    InputBox.TextColor3 = Color3.fromRGB(255, 255, 255)
                    InputBox.BackgroundTransparency = 1
                    InputBox.TextXAlignment = Enum.TextXAlignment.Left
                    InputBox.Size = UDim2.new(1, -10, 1, -6)
                    InputBox.Position = UDim2.new(0, 5, 0, 3)
                    InputBox.Parent = InputFrame
                end

                local yBtn = PanelConfig.Placeholder and 88 or 48

                local ButtonMain = Instance.new("TextButton")
                ButtonMain.Font = Enum.Font.GothamBold
                ButtonMain.Text = PanelConfig.ButtonText
                ButtonMain.TextColor3 = Color3.fromRGB(255, 255, 255)
                ButtonMain.TextSize = 12
                ButtonMain.TextTransparency = 0.3
                ButtonMain.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                ButtonMain.BackgroundTransparency = 0.935
                ButtonMain.Size = PanelConfig.SubButtonText and UDim2.new(0.5, -12, 0, 30) or UDim2.new(1, -20, 0, 30)
                ButtonMain.Position = UDim2.new(0, 10, 0, yBtn)
                ButtonMain.Parent = Panel
                Instance.new("UICorner", ButtonMain).CornerRadius = UDim.new(0, 6)

                ButtonMain.MouseButton1Click:Connect(function()
                    PanelConfig.ButtonCallback(InputBox and InputBox.Text or "")
                end)

                if PanelConfig.SubButtonText then
                    local SubButton = Instance.new("TextButton")
                    SubButton.Font = Enum.Font.GothamBold
                    SubButton.Text = PanelConfig.SubButtonText
                    SubButton.TextColor3 = Color3.fromRGB(255, 255, 255)
                    SubButton.TextSize = 12
                    SubButton.TextTransparency = 0.3
                    SubButton.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                    SubButton.BackgroundTransparency = 0.935
                    SubButton.Size = UDim2.new(0.5, -12, 0, 30)
                    SubButton.Position = UDim2.new(0.5, 2, 0, yBtn)
                    SubButton.Parent = Panel
                    Instance.new("UICorner", SubButton).CornerRadius = UDim.new(0, 6)
                    SubButton.MouseButton1Click:Connect(function()
                        PanelConfig.SubButtonCallback(InputBox and InputBox.Text or "")
                    end)
                end

                if InputBox then
                    InputBox.FocusLost:Connect(function()
                        PanelFunc.Value = InputBox.Text
                        ConfigData[configKey] = InputBox.Text
                    end)
                end

                function PanelFunc:GetInput()
                    return InputBox and InputBox.Text or ""
                end

                CountItem = CountItem + 1
                return PanelFunc
            end

            function Items:AddButton(ButtonConfig)
                ButtonConfig = ButtonConfig or {}
                ButtonConfig.Title = ButtonConfig.Title or "Confirm"
                ButtonConfig.Callback = ButtonConfig.Callback or function() end
                ButtonConfig.SubTitle = ButtonConfig.SubTitle or nil
                ButtonConfig.SubCallback = ButtonConfig.SubCallback or function() end
                ButtonConfig.Premium = ButtonConfig.Premium or false   -- << TAMBAHKAN INI

                local function CreateStyledButton(text, size, pos, callback)
                    local Btn = Instance.new("TextButton")
                    Btn.Parent = SectionAdd
                    Btn.Size = size
                    Btn.Position = pos
                    Btn.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                    Btn.BackgroundTransparency = 0.935
                    Btn.Font = Enum.Font.GothamBold
                    Btn.Text = ""
                    Btn.AutoButtonColor = false
                    Btn.LayoutOrder = CountItem

                    local Corner = Instance.new("UICorner")
                    Corner.CornerRadius = UDim.new(0, 4)
                    Corner.Parent = Btn

                    local Label = Instance.new("TextLabel")
                    Label.Parent = Btn
                    Label.Size = UDim2.new(1, -50, 1, 0)
                    Label.Position = UDim2.new(0, 12, 0, 0)
                    Label.BackgroundTransparency = 1
                    Label.Font = Enum.Font.GothamBold
                    Label.Text = text
                    Label.TextColor3 = Color3.fromRGB(255, 255, 255)
                    Label.TextSize = 13
                    Label.TextXAlignment = Enum.TextXAlignment.Left

                    local Icon = Instance.new("ImageLabel")
                    Icon.Parent = Btn
                    Icon.Size = UDim2.new(0, 20, 0, 20)
                    Icon.Position = UDim2.new(1, -32, 0.5, -10)
                    Icon.BackgroundTransparency = 1
                    Icon.Image = "rbxassetid://81380719613545"
                    Icon.ImageColor3 = Color3.fromRGB(255, 255, 255)

                    -- Premium visual lock
                    if ButtonConfig.Premium and not CheckIsPremium() then
                        Label.TextTransparency = 0.4
                        Icon.ImageTransparency = 0.5
                        Btn.BackgroundTransparency = 0.88
                    end

                    local tweenInfo = TweenInfo.new(0.1, Enum.EasingStyle.Quad, Enum.EasingDirection.Out)
                    local originalSize = size

                    Btn.MouseButton1Down:Connect(function()
                        if ButtonConfig.Premium and not CheckIsPremium() then return end
                        TweenService:Create(Btn, tweenInfo, {
                            Size = UDim2.new(
                                originalSize.X.Scale, originalSize.X.Offset - 4,
                                originalSize.Y.Scale, originalSize.Y.Offset - 4
                            ),
                            Position = UDim2.new(
                                pos.X.Scale, pos.X.Offset + 2,
                                pos.Y.Scale, pos.Y.Offset + 2
                            )
                        }):Play()
                    end)

                    Btn.MouseButton1Up:Connect(function()
                        TweenService:Create(Btn, tweenInfo, {
                            Size = originalSize,
                            Position = pos
                        }):Play()
                    end)

                    Btn.MouseLeave:Connect(function()
                        TweenService:Create(Btn, tweenInfo, {
                            Size = originalSize,
                            Position = pos
                        }):Play()
                    end)

                    Btn.MouseButton1Click:Connect(function()
                        if ButtonConfig.Premium and not CheckIsPremium() then
                            return
                        end
                        callback()
                    end)

                    -- Pasang premium tooltip
                    AttachPremiumLock(Btn, ButtonConfig.Premium)

                    return Btn
                end

                if ButtonConfig.SubTitle then
                    local ButtonFrame = Instance.new("Frame")
                    ButtonFrame.BackgroundTransparency = 1
                    ButtonFrame.Size = UDim2.new(1, 0, 0, 42)
                    ButtonFrame.LayoutOrder = CountItem
                    ButtonFrame.Parent = SectionAdd

                    local function CreateMiniButton(text, size, pos, callback)
                        local Btn = Instance.new("TextButton")
                        Btn.Parent = ButtonFrame
                        Btn.Size = size
                        Btn.Position = pos
                        Btn.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                        Btn.BackgroundTransparency = 0.935
                        Btn.Font = Enum.Font.GothamBold
                        Btn.Text = text
                        Btn.TextColor3 = Color3.fromRGB(255, 255, 255)
                        Btn.TextSize = 12
                        Btn.AutoButtonColor = false

                        local Corner = Instance.new("UICorner")
                        Corner.CornerRadius = UDim.new(0, 4)
                        Corner.Parent = Btn

                        -- Premium visual lock
                        if ButtonConfig.Premium and not CheckIsPremium() then
                            Btn.TextTransparency = 0.4
                            Btn.BackgroundTransparency = 0.88
                        end

                        local tweenInfo = TweenInfo.new(0.1, Enum.EasingStyle.Quad, Enum.EasingDirection.Out)
                        local originalSize = size

                        Btn.MouseButton1Down:Connect(function()
                            if ButtonConfig.Premium and not CheckIsPremium() then return end
                            TweenService:Create(Btn, tweenInfo, {
                                Size = UDim2.new(
                                    originalSize.X.Scale, originalSize.X.Offset - 4,
                                    originalSize.Y.Scale, originalSize.Y.Offset - 4
                                ),
                                Position = UDim2.new(
                                    pos.X.Scale, pos.X.Offset + 2,
                                    pos.Y.Scale, pos.Y.Offset + 2
                                )
                            }):Play()
                        end)

                        Btn.MouseButton1Up:Connect(function()
                            TweenService:Create(Btn, tweenInfo, {
                                Size = originalSize,
                                Position = pos
                            }):Play()
                        end)

                        Btn.MouseLeave:Connect(function()
                            TweenService:Create(Btn, tweenInfo, {
                                Size = originalSize,
                                Position = pos
                            }):Play()
                        end)

                        Btn.MouseButton1Click:Connect(function()
                            if ButtonConfig.Premium and not CheckIsPremium() then
                                return
                            end
                            callback()
                        end)

                        AttachPremiumLock(Btn, ButtonConfig.Premium)
                    end

                    CreateMiniButton(ButtonConfig.Title, UDim2.new(0.5, -3, 1, -5), UDim2.new(0, 0, 0, 2.5), ButtonConfig.Callback)
                    CreateMiniButton(ButtonConfig.SubTitle, UDim2.new(0.5, -3, 1, -5), UDim2.new(0.5, 3, 0, 2.5), ButtonConfig.SubCallback)
                else
                    CreateStyledButton(ButtonConfig.Title, UDim2.new(1, 0, 0, 42), UDim2.new(0, 0, 0, 0), ButtonConfig.Callback)
                end

                CountItem = CountItem + 1
            end

            function Items:AddToggle(ToggleConfig)
                local ToggleConfig = ToggleConfig or {}
                ToggleConfig.Title = ToggleConfig.Title or "Title"
                ToggleConfig.Content = ToggleConfig.Content or ""
                ToggleConfig.Default = ToggleConfig.Default or false
                ToggleConfig.Callback = ToggleConfig.Callback or function() end
                ToggleConfig.Keybind = ToggleConfig.Keybind or false
                ToggleConfig.KeybindDefault = ToggleConfig.KeybindDefault or "None"
                ToggleConfig.Colorpicker = ToggleConfig.Colorpicker or false
                ToggleConfig.ColorDefault = ToggleConfig.ColorDefault or Color3.fromRGB(255, 255, 255)
                ToggleConfig.ColorCallback = ToggleConfig.ColorCallback or function() end
                ToggleConfig.Premium = ToggleConfig.Premium or false

                local configKey = "Toggle_" .. ToggleConfig.Title
                local keybindKey = "Keybind_" .. ToggleConfig.Title
                local colorKey = "Color_" .. ToggleConfig.Title

                -- Sync awal dengan ConfigData
                if ConfigData[configKey] ~= nil then
                    ToggleConfig.Default = ConfigData[configKey]
                end

                -- Sync color dari ConfigData (disimpan sebagai {r,g,b})
                if ConfigData[colorKey] ~= nil then
                    local c = ConfigData[colorKey]
                    if type(c) == "table" and c.r and c.g and c.b then
                        ToggleConfig.ColorDefault = Color3.new(c.r, c.g, c.b)
                    end
                end

                local ToggleFunc = { Value = ToggleConfig.Default, Type = "Toggle" }
                ToggleFunc.Premium = ToggleConfig.Premium

                local KeybindFunc = { Value = "Keybind", Type = "Keybind" }
                local ColorpickerFunc = { Value = ToggleConfig.ColorDefault, Type = "Colorpicker" }
                local isInCallback = false
                local isListening = false

                --// Elements UI
                local Toggle = Instance.new("Frame")
                local UICorner20 = Instance.new("UICorner")
                local ToggleTitle = Instance.new("TextLabel")
                local ToggleContent = Instance.new("TextLabel")
                local ToggleButton = Instance.new("TextButton")
                local RightSide = Instance.new("Frame")
                local UIListRight = Instance.new("UIListLayout")
                local FeatureFrame2 = Instance.new("Frame")
                local UICorner22 = Instance.new("UICorner")
                local UIStroke8 = Instance.new("UIStroke")
                local ToggleCircle = Instance.new("Frame")

                --// Parent & Base Setup
                Toggle.Name = "Toggle_" .. ToggleConfig.Title
                Toggle.Parent = SectionAdd
                Toggle.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                Toggle.BackgroundTransparency = 0.935
                Toggle.BorderSizePixel = 0
                Toggle.LayoutOrder = CountItem

                UICorner20.CornerRadius = UDim.new(0, 4)
                UICorner20.Parent = Toggle

                RightSide.Name = "RightSide"
                RightSide.Size = UDim2.new(0, 150, 1, 0)
                RightSide.Position = UDim2.new(1, -12, 0, 0)
                RightSide.AnchorPoint = Vector2.new(1, 0)
                RightSide.BackgroundTransparency = 1
                RightSide.ZIndex = 10
                RightSide.Parent = Toggle

                UIListRight.Parent = RightSide
                UIListRight.FillDirection = Enum.FillDirection.Horizontal
                UIListRight.HorizontalAlignment = Enum.HorizontalAlignment.Right
                UIListRight.VerticalAlignment = Enum.VerticalAlignment.Center
                UIListRight.Padding = UDim.new(0, 8)

                --// KEYBIND LOGIC
                if ToggleConfig.Keybind then
                    local currentKey = ConfigData[keybindKey] or ToggleConfig.KeybindDefault
                    ConfigData[keybindKey] = currentKey

                    local KeyBox = Instance.new("TextButton")
                    local KeyLabel = Instance.new("TextLabel")
                    local KeyStroke = Instance.new("UIStroke")

                    KeyBox.Name = "KeyBox"
                    KeyBox.Text = ""
                    KeyBox.AutoButtonColor = false
                    KeyBox.BackgroundTransparency = 0.95
                    KeyBox.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                    KeyBox.Size = UDim2.new(0, 75, 0, 20)
                    KeyBox.LayoutOrder = 1
                    KeyBox.ZIndex = 25
                    KeyBox.Parent = RightSide
                    KeyBox.Visible = not isMobile
                    Instance.new("UICorner", KeyBox).CornerRadius = UDim.new(0, 4)

                    KeyStroke.Color = Color3.fromRGB(255, 255, 255)
                    KeyStroke.Thickness = 1
                    KeyStroke.Transparency = 0.8
                    KeyStroke.Parent = KeyBox

                    KeyLabel.Font = Enum.Font.Gotham
                    KeyLabel.TextSize = 10
                    KeyLabel.TextColor3 = Color3.fromRGB(180, 180, 180)
                    KeyLabel.BackgroundTransparency = 1
                    KeyLabel.Size = UDim2.new(1, 0, 1, 0)
                    KeyLabel.ZIndex = 26
                    KeyLabel.Parent = KeyBox

                    function KeybindFunc:Set(val)
                        val = val or "None"
                        ConfigData[keybindKey] = val
                        KeyLabel.Text = "[" .. tostring(val) .. "]"
                        KeyLabel.TextColor3 = Color3.fromRGB(180, 180, 180)

                        if UpdateKeybindEntry then
                            UpdateKeybindEntry(keybindKey, val)
                        end
                    end

                    Elements[keybindKey] = KeybindFunc
                    KeybindFunc:Set(currentKey)

                    if RegisterKeybindEntry then
                        RegisterKeybindEntry(keybindKey, ToggleConfig.Title, function()
                            return ConfigData[keybindKey] or "None"
                        end)
                    end

                    local MODIFIERS = {
                        ["LeftControl"] = "LCtrl", ["RightControl"] = "RCtrl",
                        ["LeftAlt"] = "LAlt", ["RightAlt"] = "RAlt",
                        ["LeftShift"] = "LShift", ["RightShift"] = "RShift"
                    }

                    KeyBox.MouseButton1Click:Connect(function()
                        if ToggleConfig.Premium and not CheckIsPremium() then
                            return
                        end
                        
                        if isMobile then
                            notif("Only PC can use this Feature")
                            return
                        end

                        if isListening then return end
                        isListening = true
                        KeyLabel.Text = "..."
                        KeyLabel.TextColor3 = GuiConfig.Color

                        local heldMod = nil
                        local connection
                        connection = UserInputService.InputBegan:Connect(function(inp)
                            if inp.UserInputType ~= Enum.UserInputType.Keyboard then return end
                            local rawKey = tostring(inp.KeyCode.Name)

                            if MODIFIERS[rawKey] then
                                heldMod = MODIFIERS[rawKey]
                                KeyLabel.Text = heldMod .. " + ?"
                                return
                            end

                            connection:Disconnect()
                            local finalKey = (heldMod and (heldMod .. "+" .. rawKey)) or rawKey
                            if rawKey == "Escape" or rawKey == "Unknown" then finalKey = "None" end

                            KeybindFunc:Set(finalKey)
                            task.wait(0.1)
                            isListening = false
                        end)
                    end)

                    UserInputService.InputBegan:Connect(function(input, gpe)
                        if gpe or isListening or isMobile then return end
                        local saved = ConfigData[keybindKey]
                        if not saved or saved == "None" then return end

                        local modPart, keyPart = saved:match("^(.-)%+(.+)$")
                        if not keyPart then keyPart = saved end

                        if input.KeyCode.Name == keyPart then
                            local modMatch = true
                            if modPart then
                                local isCtrl = (modPart:find("Ctrl") and (UserInputService:IsKeyDown(Enum.KeyCode.LeftControl) or UserInputService:IsKeyDown(Enum.KeyCode.RightControl)))
                                local isAlt = (modPart:find("Alt") and (UserInputService:IsKeyDown(Enum.KeyCode.LeftAlt) or UserInputService:IsKeyDown(Enum.KeyCode.RightAlt)))
                                local isShift = (modPart:find("Shift") and (UserInputService:IsKeyDown(Enum.KeyCode.LeftShift) or UserInputService:IsKeyDown(Enum.KeyCode.RightShift)))
                                if not (isCtrl or isAlt or isShift) then modMatch = false end
                            end

                            if modMatch then
                                if ToggleConfig.Premium and not CheckIsPremium() then
                                    return
                                end
                                ToggleFunc:Set(not ToggleFunc.Value)
                            end
                        end
                    end)
                end

                --// COLORPICKER LOGIC
                if ToggleConfig.Colorpicker then
                    local cpColor = ToggleConfig.ColorDefault
                    local cpOpen = false
                    local mouseDownOutside = false

                    -- Kotak kecil warna
                    local ColorBox = Instance.new("TextButton")
                    ColorBox.Name = "ColorBox"
                    ColorBox.Text = ""
                    ColorBox.AutoButtonColor = false
                    ColorBox.BackgroundColor3 = cpColor
                    ColorBox.Size = UDim2.new(0, 20, 0, 20)
                    ColorBox.LayoutOrder = 0
                    ColorBox.ZIndex = 25
                    ColorBox.Parent = RightSide
                    Instance.new("UICorner", ColorBox).CornerRadius = UDim.new(0, 4)
                    local cbStroke = Instance.new("UIStroke")
                    cbStroke.Color = Color3.fromRGB(255, 255, 255)
                    cbStroke.Thickness = 1
                    cbStroke.Transparency = 0.7
                    cbStroke.Parent = ColorBox

                    local cpScreenGui = Instance.new("ScreenGui")
                    cpScreenGui.Name = "CPGui_" .. ToggleConfig.Title
                    cpScreenGui.ZIndexBehavior = Enum.ZIndexBehavior.Sibling
                    cpScreenGui.ResetOnSpawn = false
                    cpScreenGui.DisplayOrder = 999
                    cpScreenGui.IgnoreGuiInset = true
                    cpScreenGui.Parent = CoreGui

                    local CPPopup = Instance.new("Frame")
                    CPPopup.Name = "CPPopup"
                    CPPopup.BackgroundColor3 = Color3.fromRGB(22, 22, 28)
                    CPPopup.BorderSizePixel = 0
                    CPPopup.Size = UDim2.new(0, 180, 0, 215)
                    CPPopup.ZIndex = 200
                    CPPopup.Visible = false
                    CPPopup.Active = true
                    CPPopup.ClipsDescendants = false
                    CPPopup.Parent = cpScreenGui
                    Instance.new("UICorner", CPPopup).CornerRadius = UDim.new(0, 8)
                    local cpPopStroke = Instance.new("UIStroke")
                    cpPopStroke.Color = Color3.fromRGB(255, 255, 255)
                    cpPopStroke.Thickness = 1
                    cpPopStroke.Transparency = 0.75
                    cpPopStroke.Parent = CPPopup

                    -- Hue Bar
                    local HueBar = Instance.new("Frame")
                    HueBar.Name = "HueBar"
                    HueBar.BorderSizePixel = 0
                    HueBar.Position = UDim2.new(0, 155, 0, 10)
                    HueBar.Size = UDim2.new(0, 14, 0, 140)
                    HueBar.ZIndex = 201
                    HueBar.Active = true
                    HueBar.Parent = CPPopup
                    Instance.new("UICorner", HueBar).CornerRadius = UDim.new(0, 4)
                    local hueGrad = Instance.new("UIGradient")
                    hueGrad.Rotation = 90
                    hueGrad.Color = ColorSequence.new({
                        ColorSequenceKeypoint.new(0/6, Color3.fromHSV(0/6, 1, 1)),
                        ColorSequenceKeypoint.new(1/6, Color3.fromHSV(1/6, 1, 1)),
                        ColorSequenceKeypoint.new(2/6, Color3.fromHSV(2/6, 1, 1)),
                        ColorSequenceKeypoint.new(3/6, Color3.fromHSV(3/6, 1, 1)),
                        ColorSequenceKeypoint.new(4/6, Color3.fromHSV(4/6, 1, 1)),
                        ColorSequenceKeypoint.new(5/6, Color3.fromHSV(5/6, 1, 1)),
                        ColorSequenceKeypoint.new(1,   Color3.fromHSV(1,   1, 1)),
                    })
                    hueGrad.Parent = HueBar

                    local HueCursor = Instance.new("Frame")
                    HueCursor.Name = "HueCursor"
                    HueCursor.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                    HueCursor.BorderSizePixel = 0
                    HueCursor.Size = UDim2.new(1, 4, 0, 3)
                    HueCursor.Position = UDim2.new(0, -2, 0, 0)
                    HueCursor.ZIndex = 202
                    HueCursor.Parent = HueBar
                    Instance.new("UICorner", HueCursor).CornerRadius = UDim.new(0, 2)

                    -- SV Picker
                    local SVPicker = Instance.new("Frame")
                    SVPicker.Name = "SVPicker"
                    SVPicker.BackgroundColor3 = Color3.fromHSV(0, 1, 1)
                    SVPicker.BorderSizePixel = 0
                    SVPicker.Position = UDim2.new(0, 10, 0, 10)
                    SVPicker.Size = UDim2.new(0, 135, 0, 140)
                    SVPicker.ZIndex = 201
                    SVPicker.Active = true
                    SVPicker.Parent = CPPopup
                    Instance.new("UICorner", SVPicker).CornerRadius = UDim.new(0, 4)

                    local SVSatLayer = Instance.new("Frame")
                    SVSatLayer.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                    SVSatLayer.BorderSizePixel = 0
                    SVSatLayer.Size = UDim2.new(1, 0, 1, 0)
                    SVSatLayer.ZIndex = 202
                    SVSatLayer.Parent = SVPicker
                    Instance.new("UICorner", SVSatLayer).CornerRadius = UDim.new(0, 4)
                    local satGrad = Instance.new("UIGradient")
                    satGrad.Color = ColorSequence.new({
                        ColorSequenceKeypoint.new(0, Color3.fromRGB(255, 255, 255)),
                        ColorSequenceKeypoint.new(1, Color3.fromRGB(255, 255, 255)),
                    })
                    satGrad.Transparency = NumberSequence.new({
                        NumberSequenceKeypoint.new(0, 0),
                        NumberSequenceKeypoint.new(1, 1),
                    })
                    satGrad.Parent = SVSatLayer

                    local SVValLayer = Instance.new("Frame")
                    SVValLayer.BackgroundColor3 = Color3.fromRGB(0, 0, 0)
                    SVValLayer.BorderSizePixel = 0
                    SVValLayer.Size = UDim2.new(1, 0, 1, 0)
                    SVValLayer.ZIndex = 203
                    SVValLayer.Parent = SVPicker
                    Instance.new("UICorner", SVValLayer).CornerRadius = UDim.new(0, 4)
                    local valGrad = Instance.new("UIGradient")
                    valGrad.Rotation = 90
                    valGrad.Color = ColorSequence.new({
                        ColorSequenceKeypoint.new(0, Color3.fromRGB(0, 0, 0)),
                        ColorSequenceKeypoint.new(1, Color3.fromRGB(0, 0, 0)),
                    })
                    valGrad.Transparency = NumberSequence.new({
                        NumberSequenceKeypoint.new(0, 1),
                        NumberSequenceKeypoint.new(1, 0),
                    })
                    valGrad.Parent = SVValLayer

                    local SVCursor = Instance.new("Frame")
                    SVCursor.Name = "SVCursor"
                    SVCursor.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                    SVCursor.BorderSizePixel = 0
                    SVCursor.Size = UDim2.new(0, 10, 0, 10)
                    SVCursor.AnchorPoint = Vector2.new(0.5, 0.5)
                    SVCursor.Position = UDim2.new(1, 0, 0, 0)
                    SVCursor.ZIndex = 204
                    SVCursor.Parent = SVPicker
                    Instance.new("UICorner", SVCursor).CornerRadius = UDim.new(1, 0)
                    local svCursorStroke = Instance.new("UIStroke")
                    svCursorStroke.Color = Color3.fromRGB(255, 255, 255)
                    svCursorStroke.Thickness = 1.5
                    svCursorStroke.Parent = SVCursor

                    -- Preview + Hex
                    local ColorPreview = Instance.new("Frame")
                    ColorPreview.BackgroundColor3 = cpColor
                    ColorPreview.BorderSizePixel = 0
                    ColorPreview.Position = UDim2.new(0, 10, 0, 158)
                    ColorPreview.Size = UDim2.new(0, 40, 0, 22)
                    ColorPreview.ZIndex = 201
                    ColorPreview.Parent = CPPopup
                    Instance.new("UICorner", ColorPreview).CornerRadius = UDim.new(0, 4)

                    local HexLabel = Instance.new("TextLabel")
                    HexLabel.Font = Enum.Font.GothamBold
                    HexLabel.TextSize = 11
                    HexLabel.TextColor3 = Color3.fromRGB(200, 200, 200)
                    HexLabel.BackgroundTransparency = 1
                    HexLabel.Position = UDim2.new(0, 56, 0, 158)
                    HexLabel.Size = UDim2.new(0, 110, 0, 22)
                    HexLabel.ZIndex = 201
                    HexLabel.TextXAlignment = Enum.TextXAlignment.Left
                    HexLabel.Parent = CPPopup

                    local function MakeChannelInput(label, xOffset, defaultVal)
                        local f = Instance.new("Frame")
                        f.BackgroundColor3 = Color3.fromRGB(35, 35, 45)
                        f.BorderSizePixel = 0
                        f.Position = UDim2.new(0, xOffset, 0, 186)
                        f.Size = UDim2.new(0, 50, 0, 20)
                        f.ZIndex = 201
                        f.Parent = CPPopup
                        Instance.new("UICorner", f).CornerRadius = UDim.new(0, 4)
                        local lbl = Instance.new("TextLabel")
                        lbl.Text = label
                        lbl.Font = Enum.Font.GothamBold
                        lbl.TextSize = 9
                        lbl.TextColor3 = Color3.fromRGB(150, 150, 150)
                        lbl.BackgroundTransparency = 1
                        lbl.Size = UDim2.new(0, 14, 1, 0)
                        lbl.Position = UDim2.new(0, 3, 0, 0)
                        lbl.ZIndex = 202
                        lbl.Parent = f
                        local tb = Instance.new("TextBox")
                        tb.Font = Enum.Font.GothamBold
                        tb.TextSize = 10
                        tb.TextColor3 = Color3.fromRGB(230, 230, 230)
                        tb.BackgroundTransparency = 1
                        tb.Text = tostring(defaultVal)
                        tb.Size = UDim2.new(1, -18, 1, 0)
                        tb.Position = UDim2.new(0, 16, 0, 0)
                        tb.ClearTextOnFocus = false
                        tb.ZIndex = 202
                        tb.Parent = f
                        return tb
                    end

                    local rBox = MakeChannelInput("R", 10, 255)
                    local gBox = MakeChannelInput("G", 65, 255)
                    local bBox = MakeChannelInput("B", 120, 255)

                    local currentH, currentS, currentV = Color3.toHSV(cpColor)

                    local function ColorToHex(c)
                        return string.format("#%02X%02X%02X",
                            math.floor(c.R * 255 + 0.5),
                            math.floor(c.G * 255 + 0.5),
                            math.floor(c.B * 255 + 0.5))
                    end

                    local function ApplyColor(col)
                        cpColor = col
                        ColorBox.BackgroundColor3 = col
                        ColorPreview.BackgroundColor3 = col
                        HexLabel.Text = ColorToHex(col) .. "  " .. math.floor(col.R*255+0.5) .. ", " .. math.floor(col.G*255+0.5) .. ", " .. math.floor(col.B*255+0.5)
                        rBox.Text = tostring(math.floor(col.R * 255 + 0.5))
                        gBox.Text = tostring(math.floor(col.G * 255 + 0.5))
                        bBox.Text = tostring(math.floor(col.B * 255 + 0.5))
                        ColorpickerFunc.Value = col
                        ConfigData[colorKey] = { r = col.R, g = col.G, b = col.B }
                        pcall(ToggleConfig.ColorCallback, col)
                    end

                    local function UpdateFromHSV()
                        local col = Color3.fromHSV(currentH, currentS, currentV)
                        SVPicker.BackgroundColor3 = Color3.fromHSV(currentH, 1, 1)
                        SVCursor.Position = UDim2.new(currentS, 0, 1 - currentV, 0)
                        HueCursor.Position = UDim2.new(0, -2, currentH, -1.5)
                        ApplyColor(col)
                    end

                    UpdateFromHSV()

                    local function UpdatePopupPosition()
                        local abs = ColorBox.AbsolutePosition
                        local absSize = ColorBox.AbsoluteSize
                        local popW, popH = 180, 215
                        local vp = workspace.CurrentCamera.ViewportSize

                        local px = abs.X + absSize.X - popW
                        local py = abs.Y + absSize.Y + 6

                        if py + popH > vp.Y - 8 then
                            py = abs.Y - popH - 6
                        end

                        if py < 8 then
                            py = math.max(8, (vp.Y - popH) / 2)
                        end

                        if px < 8 then
                            px = 8
                        elseif px + popW > vp.X - 8 then
                            px = vp.X - popW - 8
                        end

                        py = math.clamp(py, 8, vp.Y - popH - 8)
                        CPPopup.Position = UDim2.new(0, px, 0, py)
                    end

                    local svDragging = false
                    SVPicker.InputBegan:Connect(function(inp)
                        if inp.UserInputType == Enum.UserInputType.MouseButton1 or inp.UserInputType == Enum.UserInputType.Touch then
                            svDragging = true
                        end
                    end)

                    -- Drag Hue
                    local hueDragging = false
                    HueBar.InputBegan:Connect(function(inp)
                        if inp.UserInputType == Enum.UserInputType.MouseButton1 or inp.UserInputType == Enum.UserInputType.Touch then
                            hueDragging = true
                        end
                    end)

                    UserInputService.InputEnded:Connect(function(inp)
                        if inp.UserInputType == Enum.UserInputType.MouseButton1 or inp.UserInputType == Enum.UserInputType.Touch then
                            svDragging = false
                            hueDragging = false
                        end
                    end)

                    UserInputService.InputChanged:Connect(function(inp)
                        if not (svDragging or hueDragging) then return end
                        if inp.UserInputType ~= Enum.UserInputType.MouseMovement and inp.UserInputType ~= Enum.UserInputType.Touch then return end

                        if svDragging then
                            local px = inp.Position.X - SVPicker.AbsolutePosition.X
                            local py = inp.Position.Y - SVPicker.AbsolutePosition.Y
                            currentS = math.clamp(px / SVPicker.AbsoluteSize.X, 0, 1)
                            currentV = math.clamp(1 - (py / SVPicker.AbsoluteSize.Y), 0, 1)
                            UpdateFromHSV()
                        elseif hueDragging then
                            local py = inp.Position.Y - HueBar.AbsolutePosition.Y
                            currentH = math.clamp(py / HueBar.AbsoluteSize.Y, 0, 1)
                            UpdateFromHSV()
                        end
                    end)

                    -- RGB
                    local function OnRGBInput()
                        local r = math.clamp(tonumber(rBox.Text) or 0, 0, 255) / 255
                        local g = math.clamp(tonumber(gBox.Text) or 0, 0, 255) / 255
                        local b = math.clamp(tonumber(bBox.Text) or 0, 0, 255) / 255
                        local col = Color3.new(r, g, b)
                        currentH, currentS, currentV = Color3.toHSV(col)
                        UpdateFromHSV()
                    end
                    rBox.FocusLost:Connect(OnRGBInput)
                    gBox.FocusLost:Connect(OnRGBInput)
                    bBox.FocusLost:Connect(OnRGBInput)

                    -- Backdrop
                    local CPBackdrop = Instance.new("TextButton")
                    CPBackdrop.Name = "CPBackdrop"
                    CPBackdrop.Text = ""
                    CPBackdrop.AutoButtonColor = false
                    CPBackdrop.BackgroundTransparency = 1
                    CPBackdrop.Size = UDim2.new(1, 0, 1, 0)
                    CPBackdrop.ZIndex = 199
                    CPBackdrop.Visible = false
                    CPBackdrop.Active = true
                    CPBackdrop.Modal = true
                    CPBackdrop.Parent = cpScreenGui

                    local function SetPopupOpen(open)
                        cpOpen = open
                        if open then
                            UpdatePopupPosition()
                        end
                        CPPopup.Visible = open
                        CPBackdrop.Visible = open
                    end

                    -- Tutup HANYA lewat klik backdrop (paling aman)
                    CPBackdrop.MouseButton1Click:Connect(function()
                        -- Jangan tutup kalau lagi drag
                        if svDragging or hueDragging then return end
                        SetPopupOpen(false)
                    end)

                    -- Backup: kalau user klik di luar (bukan drag)
                    local pressStartedOutside = false

                    UserInputService.InputBegan:Connect(function(input)
                        if not cpOpen then return end
                        if input.UserInputType ~= Enum.UserInputType.MouseButton1 
                        and input.UserInputType ~= Enum.UserInputType.Touch then return end

                        local mp = input.Position
                        local pp = CPPopup.AbsolutePosition
                        local ps = CPPopup.AbsoluteSize
                        local inside = mp.X >= pp.X and mp.X <= pp.X + ps.X 
                                and mp.Y >= pp.Y and mp.Y <= pp.Y + ps.Y

                        pressStartedOutside = not inside
                    end)

                    UserInputService.InputEnded:Connect(function(input)
                        if not cpOpen then return end
                        if input.UserInputType ~= Enum.UserInputType.MouseButton1 
                        and input.UserInputType ~= Enum.UserInputType.Touch then return end

                        -- Kalau lagi drag, jangan tutup
                        if svDragging or hueDragging then 
                            pressStartedOutside = false
                            return 
                        end

                        if pressStartedOutside then
                            local mp = input.Position
                            local pp = CPPopup.AbsolutePosition
                            local ps = CPPopup.AbsoluteSize
                            local stillOutside = not (mp.X >= pp.X and mp.X <= pp.X + ps.X 
                                                and mp.Y >= pp.Y and mp.Y <= pp.Y + ps.Y)

                            if stillOutside then
                                SetPopupOpen(false)
                            end
                        end
                        pressStartedOutside = false
                    end)

                    ColorBox.MouseButton1Click:Connect(function()
                        SetPopupOpen(not cpOpen)
                    end)

                    function ColorpickerFunc:Set(col)
                        if typeof(col) == "Color3" then
                        elseif type(col) == "table" and col.r and col.g and col.b then
                            col = Color3.new(col.r, col.g, col.b)
                        else
                            return
                        end
                        currentH, currentS, currentV = Color3.toHSV(col)
                        UpdateFromHSV()
                    end

                    function ColorpickerFunc:Get()
                        return cpColor
                    end

                    Elements[colorKey] = ColorpickerFunc
                end

                --// TOGGLE VISUAL
                FeatureFrame2.Name = "ToggleVisual"
                FeatureFrame2.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                FeatureFrame2.BackgroundTransparency = 0.92
                FeatureFrame2.Size = UDim2.new(0, 30, 0, 15)
                FeatureFrame2.LayoutOrder = 2
                FeatureFrame2.Parent = RightSide

                UICorner22.CornerRadius = UDim.new(0, 10)
                UICorner22.Parent = FeatureFrame2

                UIStroke8.Color = Color3.fromRGB(255, 255, 255)
                UIStroke8.Thickness = 1.5
                UIStroke8.Transparency = 0.9
                UIStroke8.Parent = FeatureFrame2

                ToggleCircle.BackgroundColor3 = Color3.fromRGB(230, 230, 230)
                ToggleCircle.Size = UDim2.new(0, 12, 0, 12)
                ToggleCircle.Position = UDim2.new(0, 2, 0.5, -6)
                ToggleCircle.Parent = FeatureFrame2
                Instance.new("UICorner", ToggleCircle).CornerRadius = UDim.new(1, 0)

                --// TEXT LABELS
                ToggleTitle.Font = Enum.Font.GothamBold
                ToggleTitle.Text = ToggleConfig.Title
                ToggleTitle.TextSize = 13
                ToggleTitle.TextColor3 = Color3.fromRGB(231, 231, 231)
                ToggleTitle.TextXAlignment = Enum.TextXAlignment.Left
                ToggleTitle.TextYAlignment = Enum.TextYAlignment.Top
                ToggleTitle.BackgroundTransparency = 1
                ToggleTitle.Position = UDim2.new(0, 10, 0, 8)
                ToggleTitle.Size = UDim2.new(1, -160, 0, 13)
                ToggleTitle.Parent = Toggle
                ToggleTitle.RichText = true

                ToggleContent.Font = Enum.Font.GothamBold
                ToggleContent.Text = ToggleConfig.Content
                ToggleContent.TextColor3 = Color3.fromRGB(255, 255, 255)
                ToggleContent.TextSize = 12
                ToggleContent.TextTransparency = 0.6
                ToggleContent.TextXAlignment = Enum.TextXAlignment.Left
                ToggleContent.TextYAlignment = Enum.TextYAlignment.Top
                ToggleContent.BackgroundTransparency = 1
                ToggleContent.Position = UDim2.new(0, 10, 0, 24)
                ToggleContent.Size = UDim2.new(1, -160, 0, 12)
                ToggleContent.TextWrapped = true
                ToggleContent.Parent = Toggle

                local function UpdateHeight()
                    local textWidth = ToggleContent.AbsoluteSize.X
                    if textWidth <= 0 then textWidth = 350 end 
                    
                    local bounds = TextService:GetTextSize(
                        ToggleContent.Text,
                        ToggleContent.TextSize,
                        ToggleContent.Font,
                        Vector2.new(textWidth, 1000)
                    )
                    
                    local contentHeight = bounds.Y
                    ToggleContent.Size = UDim2.new(1, -160, 0, contentHeight)
                    
                    Toggle.Size = UDim2.new(1, 0, 0, contentHeight + 28)
                    if UpdateSizeSection then UpdateSizeSection() end
                end

                ToggleContent:GetPropertyChangedSignal("AbsoluteSize"):Connect(UpdateHeight)
                UpdateHeight()

                --// MAIN BUTTON OVERLAY
                ToggleButton.BackgroundTransparency = 1
                ToggleButton.Size = UDim2.new(1, 0, 1, 0)
                ToggleButton.ZIndex = 5
                ToggleButton.Text = ""
                ToggleButton.Parent = Toggle

                ToggleButton.Activated:Connect(function()
                    if ToggleConfig.Premium and not CheckIsPremium() then
                        return
                    end
                    if not isListening then
                        ToggleFunc:Set(not ToggleFunc.Value)
                    end
                end)

                if ToggleConfig.Premium and not CheckIsPremium() then
                    AttachPremiumLock(ToggleButton, true)

                    if not CheckIsPremium() then
                        FeatureFrame2.BackgroundColor3 = Color3.fromRGB(50, 50, 60)
                        FeatureFrame2.BackgroundTransparency = 0.85
                        UIStroke8.Color = Color3.fromRGB(90, 90, 100)
                        UIStroke8.Transparency = 0.7

                        ToggleCircle.BackgroundColor3 = Color3.fromRGB(80, 80, 90)
                        ToggleTitle.TextTransparency = 0.4
                        ToggleContent.TextTransparency = 0.55
                    end
                end

                --// TOGGLE FUNC SET
                function ToggleFunc:Set(Value, IgnoreCheck, SkipCallback)
                    if not IgnoreCheck and ToggleFunc.Value == Value then return end

                    ToggleFunc.Value = Value
                    ConfigData[configKey] = Value

                    if Value then
                        TweenService:Create(ToggleTitle, TweenInfo.new(0.2), { TextColor3 = GuiConfig.Color }):Play()
                        TweenService:Create(ToggleCircle, TweenInfo.new(0.2), { Position = UDim2.new(1, -14, 0.5, -6) }):Play()
                        TweenService:Create(FeatureFrame2, TweenInfo.new(0.2), { BackgroundColor3 = GuiConfig.Color, BackgroundTransparency = 0 }):Play()
                        TweenService:Create(UIStroke8, TweenInfo.new(0.2), { Transparency = 1 }):Play()
                    else
                        TweenService:Create(ToggleTitle, TweenInfo.new(0.2), { TextColor3 = Color3.fromRGB(231, 231, 231) }):Play()
                        TweenService:Create(ToggleCircle, TweenInfo.new(0.2), { Position = UDim2.new(0, 2, 0.5, -6) }):Play()
                        TweenService:Create(FeatureFrame2, TweenInfo.new(0.2), { BackgroundColor3 = Color3.fromRGB(255, 255, 255), BackgroundTransparency = 0.92 }):Play()
                        TweenService:Create(UIStroke8, TweenInfo.new(0.2), { Transparency = 0.9 }):Play()
                    end

                    if not SkipCallback then
                        if not isInCallback then
                            isInCallback = true
                            task.spawn(function()
                                pcall(ToggleConfig.Callback, Value)
                                task.wait(0.05)
                                isInCallback = false
                            end)
                        end
                    end
                end

                -- Finalize
                ToggleFunc:Set(ToggleFunc.Value, true, true)
                if ToggleFunc.Value == true then
                    task.defer(function()
                        pcall(ToggleConfig.Callback, true)
                    end)
                end

                CountItem = CountItem + 1
                Elements[configKey] = ToggleFunc

                if ToggleConfig.Colorpicker then
                    ToggleFunc.Colorpicker = ColorpickerFunc
                    return ToggleFunc, ColorpickerFunc
                end

                return ToggleFunc
            end

            function Items:AddKeybind(KeybindConfig)
                KeybindConfig = KeybindConfig or {}
                KeybindConfig.Title = KeybindConfig.Title or "Keybind"
                KeybindConfig.Default = KeybindConfig.Default or Enum.KeyCode.None
                KeybindConfig.Callback = KeybindConfig.Callback or function() end
                KeybindConfig.Premium = KeybindConfig.Premium or false

                local configKey = "Keybind_" .. KeybindConfig.Title
                if ConfigData[configKey] ~= nil then
                    if type(ConfigData[configKey]) == "string" then
                        KeybindConfig.Default = Enum.KeyCode[ConfigData[configKey]] or KeybindConfig.Default
                    end
                end

                local KeybindFunc = { Value = KeybindConfig.Default }

                local Keybind = Instance.new("Frame")
                Keybind.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                Keybind.BackgroundTransparency = 0.935
                Keybind.BorderSizePixel = 0
                Keybind.LayoutOrder = CountItem
                Keybind.Size = UDim2.new(1, 0, 0, 38)
                Keybind.Name = "Keybind"
                Keybind.Parent = SectionAdd

                local UICorner = Instance.new("UICorner")
                UICorner.CornerRadius = UDim.new(0, 4)
                UICorner.Parent = Keybind

                local Title = Instance.new("TextLabel")
                Title.Font = Enum.Font.GothamBold
                Title.Text = KeybindConfig.Title
                Title.TextColor3 = Color3.fromRGB(255, 255, 255)
                Title.TextSize = 13
                Title.TextXAlignment = Enum.TextXAlignment.Left
                Title.BackgroundTransparency = 1
                Title.Position = UDim2.new(0, 10, 0, 0)
                Title.Size = UDim2.new(1, -110, 1, 0)
                Title.Name = "KeybindTitle"
                Title.Parent = Keybind

                local KeybindBtn = Instance.new("TextButton")
                KeybindBtn.Font = Enum.Font.GothamBold
                KeybindBtn.Text = KeybindConfig.Default.Name
                KeybindBtn.TextColor3 = Color3.fromRGB(200, 200, 200)
                KeybindBtn.TextSize = 12
                KeybindBtn.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                KeybindBtn.BackgroundTransparency = 0.9
                KeybindBtn.AnchorPoint = Vector2.new(1, 0.5)
                KeybindBtn.Position = UDim2.new(1, -10, 0.5, 0)
                KeybindBtn.Size = UDim2.new(0, 80, 0, 24)
                KeybindBtn.Parent = Keybind

                local BtnCorner = Instance.new("UICorner")
                BtnCorner.CornerRadius = UDim.new(0, 4)
                BtnCorner.Parent = KeybindBtn

                local Binding = false

                KeybindBtn.MouseButton1Click:Connect(function()
                    if isMobile then return end
                    if KeybindConfig.Premium and not CheckIsPremium() then return end

                    Binding = true
                    KeybindBtn.Text = "..."
                    KeybindBtn.TextColor3 = GuiConfig.Color
                end)

                UserInputService.InputBegan:Connect(function(input, gpe)
                    -- Hapus/abaikan filter gpe saat sedang proses Binding biar Esc tetap terbaca
                    if Binding then
                        -- Jika pencet Escape, set keybind jadi None
                        if input.KeyCode == Enum.KeyCode.Escape then
                            Binding = false
                            KeybindConfig.Default = Enum.KeyCode.None
                            KeybindFunc.Value = Enum.KeyCode.None
                            KeybindBtn.Text = "None"
                            KeybindBtn.TextColor3 = Color3.fromRGB(200, 200, 200)
                            ConfigData[configKey] = "None"
                            KeybindConfig.Callback(Enum.KeyCode.None)
                            return
                        end

                        -- Jika input keyboard biasa selain Escape
                        if input.UserInputType == Enum.UserInputType.Keyboard then
                            Binding = false
                            KeybindConfig.Default = input.KeyCode
                            KeybindFunc.Value = input.KeyCode
                            KeybindBtn.Text = input.KeyCode.Name
                            KeybindBtn.TextColor3 = Color3.fromRGB(200, 200, 200)
                            ConfigData[configKey] = input.KeyCode.Name
                            KeybindConfig.Callback(input.KeyCode)
                        end
                    elseif not gpe and input.UserInputType == Enum.UserInputType.Keyboard and input.KeyCode == KeybindFunc.Value and KeybindFunc.Value ~= Enum.KeyCode.None then
                        if KeybindConfig.Premium and not CheckIsPremium() then return end
                        KeybindConfig.Callback(KeybindFunc.Value)
                    end
                end)

                function KeybindFunc:Set(key)
                    if type(key) == "string" then
                        key = Enum.KeyCode[key] or Enum.KeyCode.None
                    end
                    KeybindConfig.Default = key
                    KeybindFunc.Value = key
                    KeybindBtn.Text = key.Name
                    ConfigData[configKey] = key.Name
                    
                    -- Tambahkan baris ini agar Callback (SetToggleKey) ikut ter-trigger saat LoadConfig!
                    KeybindConfig.Callback(key)
                end

                Elements[configKey] = {
                    Set = function(self, val) KeybindFunc:Set(val) end,
                    Type = "Keybind",
                    Premium = KeybindConfig.Premium
                }

                AttachPremiumLock(KeybindBtn, KeybindConfig.Premium)

                CountItem = CountItem + 1
                return KeybindFunc
            end
        
            function Items:AddCheckbox(CheckboxConfig)
                local CheckboxConfig = CheckboxConfig or {}
                CheckboxConfig.Title = CheckboxConfig.Title or "Checkbox"
                CheckboxConfig.Content = CheckboxConfig.Content or ""
                CheckboxConfig.Default = CheckboxConfig.Default or false
                CheckboxConfig.Callback = CheckboxConfig.Callback or function() end
                CheckboxConfig.Premium = CheckboxConfig.Premium or false

                local configKey = "Checkbox_" .. CheckboxConfig.Title
                if ConfigData[configKey] ~= nil then
                    CheckboxConfig.Default = ConfigData[configKey]
                end

                local CheckboxFunc = { Value = CheckboxConfig.Default, Type = "Toggle" }
                CheckboxFunc.Premium = CheckboxConfig.Premium

                local Checkbox = Instance.new("Frame")
                local UICorner20 = Instance.new("UICorner")
                local CheckboxTitle = Instance.new("TextLabel")
                local CheckboxContent = Instance.new("TextLabel")
                local CheckboxButton = Instance.new("TextButton")
                local RightSide = Instance.new("Frame")
                local CheckboxVisual = Instance.new("Frame")
                local UICornerVisual = Instance.new("UICorner")
                local UIStrokeVisual = Instance.new("UIStroke")
                local Checkmark = Instance.new("ImageLabel")

                Checkbox.Name = "Checkbox_" .. CheckboxConfig.Title
                Checkbox.Parent = SectionAdd
                Checkbox.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                Checkbox.BackgroundTransparency = 0.935
                Checkbox.BorderSizePixel = 0
                Checkbox.LayoutOrder = CountItem
                Checkbox.Size = UDim2.new(1, 0, 0, 50)

                UICorner20.CornerRadius = UDim.new(0, 4)
                UICorner20.Parent = Checkbox

                -- Right Side Container (sejajarkan dengan toggle)
                RightSide.Name = "RightSide"
                RightSide.Size = UDim2.new(0, 32, 1, 0)
                RightSide.Position = UDim2.new(1, -16, 0, 0)   -- Disesuaikan
                RightSide.AnchorPoint = Vector2.new(1, 0)
                RightSide.BackgroundTransparency = 1
                RightSide.Parent = Checkbox

                -- Checkbox Box
                CheckboxVisual.Name = "CheckboxVisual"
                CheckboxVisual.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                CheckboxVisual.BackgroundTransparency = 0.92
                CheckboxVisual.Size = UDim2.new(0, 22, 0, 22)
                CheckboxVisual.Position = UDim2.new(0.5, -11, 0.5, -11)
                CheckboxVisual.Parent = RightSide

                UICornerVisual.CornerRadius = UDim.new(0, 5)
                UICornerVisual.Parent = CheckboxVisual

                UIStrokeVisual.Color = Color3.fromRGB(180, 180, 180)
                UIStrokeVisual.Thickness = 1.6
                UIStrokeVisual.Transparency = 0.75
                UIStrokeVisual.Parent = CheckboxVisual

                -- Checkmark
                Checkmark.Name = "Checkmark"
                Checkmark.Image = "rbxassetid://6031094678"
                Checkmark.ImageColor3 = GuiConfig.Color
                Checkmark.ImageTransparency = 1
                Checkmark.BackgroundTransparency = 1
                Checkmark.Size = UDim2.new(0, 18, 0, 18)
                Checkmark.Position = UDim2.new(0.5, -9, 0.5, -9)
                Checkmark.Parent = CheckboxVisual

                -- Text Labels
                CheckboxTitle.Font = Enum.Font.GothamBold
                CheckboxTitle.Text = CheckboxConfig.Title
                CheckboxTitle.TextSize = 13
                CheckboxTitle.TextColor3 = Color3.fromRGB(231, 231, 231)
                CheckboxTitle.TextXAlignment = Enum.TextXAlignment.Left
                CheckboxTitle.BackgroundTransparency = 1
                CheckboxTitle.Position = UDim2.new(0, 10, 0, 8)
                CheckboxTitle.Size = UDim2.new(1, -70, 0, 13)
                CheckboxTitle.Parent = Checkbox
                CheckboxTitle.RichText = true

                CheckboxContent.Font = Enum.Font.GothamBold
                CheckboxContent.Text = CheckboxConfig.Content
                CheckboxContent.TextColor3 = Color3.fromRGB(255, 255, 255)
                CheckboxContent.TextSize = 12
                CheckboxContent.TextTransparency = 0.6
                CheckboxContent.TextXAlignment = Enum.TextXAlignment.Left
                CheckboxContent.BackgroundTransparency = 1
                CheckboxContent.Position = UDim2.new(0, 10, 0, 24)
                CheckboxContent.Size = UDim2.new(1, -70, 0, 12)
                CheckboxContent.TextWrapped = true
                CheckboxContent.Parent = Checkbox

                local function UpdateHeight()
                    local textWidth = CheckboxContent.AbsoluteSize.X
                    if textWidth <= 0 then textWidth = 350 end 
                    
                    local bounds = TextService:GetTextSize(
                        CheckboxContent.Text,
                        CheckboxContent.TextSize,
                        CheckboxContent.Font,
                        Vector2.new(textWidth, 1000)
                    )
                    
                    local contentHeight = bounds.Y
                    CheckboxContent.Size = UDim2.new(1, -70, 0, contentHeight)
                    
                    Checkbox.Size = UDim2.new(1, 0, 0, contentHeight + 38)
                    if UpdateSizeSection then UpdateSizeSection() end
                end

                CheckboxContent:GetPropertyChangedSignal("AbsoluteSize"):Connect(UpdateHeight)
                UpdateHeight()

                CheckboxButton.BackgroundTransparency = 1
                CheckboxButton.Size = UDim2.new(1, 0, 1, 0)
                CheckboxButton.ZIndex = 5
                CheckboxButton.Text = ""
                CheckboxButton.Parent = Checkbox

                CheckboxButton.Activated:Connect(function()
                    if CheckboxConfig.Premium and not CheckIsPremium() then
                        return
                    end
                    CheckboxFunc:Set(not CheckboxFunc.Value)
                end)

                if CheckboxConfig.Premium and not CheckIsPremium() then
                    AttachPremiumLock(CheckboxButton, true)

                    if not CheckIsPremium() then
                        CheckboxVisual.BackgroundColor3 = Color3.fromRGB(50, 50, 60)
                        CheckboxVisual.BackgroundTransparency = 0.85
                        UIStrokeVisual.Color = Color3.fromRGB(90, 90, 100)
                        UIStrokeVisual.Transparency = 0.7

                        Checkmark.ImageTransparency = 0.7
                        CheckboxTitle.TextTransparency = 0.4
                        CheckboxContent.TextTransparency = 0.55
                    end
                end

                function CheckboxFunc:Set(Value, IgnoreCheck, SkipCallback)
                    if not IgnoreCheck and CheckboxFunc.Value == Value then return end
                    
                    CheckboxFunc.Value = Value
                    ConfigData[configKey] = Value

                    if Value then
                        TweenService:Create(CheckboxTitle, TweenInfo.new(0.2), { TextColor3 = GuiConfig.Color }):Play()
                        TweenService:Create(Checkmark, TweenInfo.new(0.2), { ImageTransparency = 0 }):Play()
                    else
                        TweenService:Create(CheckboxTitle, TweenInfo.new(0.2), { TextColor3 = Color3.fromRGB(231, 231, 231) }):Play()
                        TweenService:Create(Checkmark, TweenInfo.new(0.2), { ImageTransparency = 1 }):Play()
                    end

                    if not SkipCallback then
                        pcall(CheckboxConfig.Callback, Value)
                    end
                end

                CheckboxFunc:Set(CheckboxFunc.Value, true, true)
                if CheckboxFunc.Value == true then
                    task.defer(function()
                        pcall(CheckboxConfig.Callback, true)
                    end)
                end

                CountItem = CountItem + 1
                Elements[configKey] = CheckboxFunc

                return CheckboxFunc
            end

            function Items:AddSlider(SliderConfig)
                local SliderConfig = SliderConfig or {}
                SliderConfig.Title = SliderConfig.Title or "Slider"
                SliderConfig.Content = SliderConfig.Content or ""
                SliderConfig.Increment = SliderConfig.Increment or 1
                SliderConfig.Min = SliderConfig.Min or 0
                SliderConfig.Max = SliderConfig.Max or 100
                SliderConfig.Default = SliderConfig.Default or 50
                SliderConfig.Callback = SliderConfig.Callback or function() end

                local configKey = "Slider_" .. SliderConfig.Title
                if ConfigData[configKey] ~= nil then
                    SliderConfig.Default = ConfigData[configKey]
                end

                local SliderFunc = { Value = SliderConfig.Default }

                local Slider = Instance.new("Frame");
                local UICorner15 = Instance.new("UICorner");
                local SliderTitle = Instance.new("TextLabel");
                local SliderContent = Instance.new("TextLabel");
                local SliderInput = Instance.new("Frame");
                local UICorner16 = Instance.new("UICorner");
                local TextBox = Instance.new("TextBox");
                local SliderFrame = Instance.new("Frame");
                local UICorner17 = Instance.new("UICorner");
                local SliderDraggable = Instance.new("Frame");
                local UICorner18 = Instance.new("UICorner");
                local UIStroke5 = Instance.new("UIStroke");
                local SliderCircle = Instance.new("Frame");
                local UICorner19 = Instance.new("UICorner");
                local UIStroke6 = Instance.new("UIStroke");
                local UIStroke7 = Instance.new("UIStroke");

                Slider.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                Slider.BackgroundTransparency = 0.9350000023841858
                Slider.BorderColor3 = Color3.fromRGB(0, 0, 0)
                Slider.BorderSizePixel = 0
                Slider.LayoutOrder = CountItem
                Slider.Size = UDim2.new(1, 0, 0, 46)
                Slider.Name = "Slider"
                Slider.Parent = SectionAdd

                UICorner15.CornerRadius = UDim.new(0, 4)
                UICorner15.Parent = Slider

                SliderTitle.Font = Enum.Font.GothamBold
                SliderTitle.Text = SliderConfig.Title
                SliderTitle.TextColor3 = Color3.fromRGB(230.77499270439148, 230.77499270439148, 230.77499270439148)
                SliderTitle.TextSize = 13
                SliderTitle.TextXAlignment = Enum.TextXAlignment.Left
                SliderTitle.TextYAlignment = Enum.TextYAlignment.Top
                SliderTitle.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                SliderTitle.BackgroundTransparency = 0.9990000128746033
                SliderTitle.BorderColor3 = Color3.fromRGB(0, 0, 0)
                SliderTitle.BorderSizePixel = 0
                SliderTitle.Position = UDim2.new(0, 10, 0, 10)
                SliderTitle.Size = UDim2.new(1, -180, 0, 13)
                SliderTitle.Name = "SliderTitle"
                SliderTitle.Parent = Slider

                SliderContent.Font = Enum.Font.GothamBold
                SliderContent.Text = SliderConfig.Content
                SliderContent.TextColor3 = Color3.fromRGB(255, 255, 255)
                SliderContent.TextSize = 12
                SliderContent.TextTransparency = 0.6000000238418579
                SliderContent.TextXAlignment = Enum.TextXAlignment.Left
                SliderContent.TextYAlignment = Enum.TextYAlignment.Bottom
                SliderContent.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                SliderContent.BackgroundTransparency = 0.9990000128746033
                SliderContent.BorderColor3 = Color3.fromRGB(0, 0, 0)
                SliderContent.BorderSizePixel = 0
                SliderContent.Position = UDim2.new(0, 10, 0, 25)
                SliderContent.Size = UDim2.new(1, -180, 0, 12)
                SliderContent.Name = "SliderContent"
                SliderContent.Parent = Slider

                SliderContent.Size = UDim2.new(1, -180, 0,
                    12 + (12 * (SliderContent.TextBounds.X // SliderContent.AbsoluteSize.X)))
                SliderContent.TextWrapped = true
                Slider.Size = UDim2.new(1, 0, 0, SliderContent.AbsoluteSize.Y + 33)

                SliderContent:GetPropertyChangedSignal("AbsoluteSize"):Connect(function()
                    SliderContent.TextWrapped = false
                    SliderContent.Size = UDim2.new(1, -180, 0,
                        12 + (12 * (SliderContent.TextBounds.X // SliderContent.AbsoluteSize.X)))
                    Slider.Size = UDim2.new(1, 0, 0, SliderContent.AbsoluteSize.Y + 33)
                    SliderContent.TextWrapped = true
                    UpdateSizeSection()
                end)

                SliderInput.AnchorPoint = Vector2.new(0, 0.5)
                SliderInput.BackgroundColor3 = GuiConfig.Color
                SliderInput.BorderColor3 = Color3.fromRGB(0, 0, 0)
                SliderInput.BackgroundTransparency = 1
                SliderInput.BorderSizePixel = 0
                SliderInput.Position = UDim2.new(1, -155, 0.5, 0)
                SliderInput.Size = UDim2.new(0, 28, 0, 20)
                SliderInput.Name = "SliderInput"
                SliderInput.Parent = Slider

                UICorner16.CornerRadius = UDim.new(0, 2)
                UICorner16.Parent = SliderInput

                TextBox.Font = Enum.Font.GothamBold
                TextBox.Text = "90"
                TextBox.TextColor3 = Color3.fromRGB(255, 255, 255)
                TextBox.TextSize = 12
                TextBox.TextWrapped = true
                TextBox.ClearTextOnFocus = false
                TextBox.BackgroundColor3 = Color3.fromRGB(0, 0, 0)
                TextBox.BackgroundTransparency = 0.9990000128746033
                TextBox.BorderColor3 = Color3.fromRGB(0, 0, 0)
                TextBox.BorderSizePixel = 0
                TextBox.Position = UDim2.new(0, -1, 0, 0)
                TextBox.Size = UDim2.new(1, 0, 1, 0)
                TextBox.Parent = SliderInput

                SliderFrame.AnchorPoint = Vector2.new(1, 0.5)
                SliderFrame.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                SliderFrame.BackgroundTransparency = 0.800000011920929
                SliderFrame.BorderColor3 = Color3.fromRGB(0, 0, 0)
                SliderFrame.BorderSizePixel = 0
                SliderFrame.Position = UDim2.new(1, -20, 0.5, 0)
                SliderFrame.Size = UDim2.new(0, 100, 0, 3)
                SliderFrame.Name = "SliderFrame"
                SliderFrame.Parent = Slider

                local SliderHitbox = Instance.new("Frame")
                SliderHitbox.BackgroundTransparency = 1
                SliderHitbox.AnchorPoint = Vector2.new(1, 0.5)
                SliderHitbox.Position = UDim2.new(1, -20, 0.5, 0)
                SliderHitbox.Size = UDim2.new(0, 100, 0, 40)  -- 40px tinggi, gampang kesentuh
                SliderHitbox.ZIndex = SliderFrame.ZIndex + 1
                SliderHitbox.Parent = Slider

                UICorner17.Parent = SliderFrame

                SliderDraggable.AnchorPoint = Vector2.new(0, 0.5)
                SliderDraggable.BackgroundColor3 = GuiConfig.Color
                SliderDraggable.BorderColor3 = Color3.fromRGB(0, 0, 0)
                SliderDraggable.BorderSizePixel = 0
                SliderDraggable.Position = UDim2.new(0, 0, 0.5, 0)
                SliderDraggable.Size = UDim2.new(0.899999976, 0, 0, 1)
                SliderDraggable.Name = "SliderDraggable"
                SliderDraggable.Parent = SliderFrame

                UICorner18.Parent = SliderDraggable

                SliderCircle.AnchorPoint = Vector2.new(1, 0.5)
                SliderCircle.BackgroundColor3 = GuiConfig.Color
                SliderCircle.BorderColor3 = Color3.fromRGB(0, 0, 0)
                SliderCircle.BorderSizePixel = 0
                SliderCircle.Position = UDim2.new(1, 4, 0.5, 0)
                SliderCircle.Size = UDim2.new(0, 14, 0, 14)
                SliderCircle.Name = "SliderCircle"
                SliderCircle.Parent = SliderDraggable

                UICorner19.Parent = SliderCircle

                UIStroke6.Color = GuiConfig.Color
                UIStroke6.Parent = SliderCircle

                local Dragging = false
                local function Round(Number, Factor)
                    if Factor == 0 then return Number end
                    return math.floor(Number / Factor + 0.5) * Factor
                end
                
                function SliderFunc:Set(Value)
                    Value = math.clamp(Round(Value, SliderConfig.Increment), SliderConfig.Min, SliderConfig.Max)
                    SliderFunc.Value = Value
                    TextBox.Text = tostring(Value)
                    TweenService:Create(
                        SliderDraggable,
                        TweenInfo.new(0.3, Enum.EasingStyle.Quad, Enum.EasingDirection.Out),
                        { Size = UDim2.fromScale((Value - SliderConfig.Min) / (SliderConfig.Max - SliderConfig.Min), 1) }
                    ):Play()

                    SliderConfig.Callback(Value)
                    ConfigData[configKey] = Value
                    -- SaveConfig()
                end

                SliderHitbox.InputBegan:Connect(function(Input)
                    if Input.UserInputType == Enum.UserInputType.MouseButton1 or Input.UserInputType == Enum.UserInputType.Touch then
                        Dragging = true
                        TweenService:Create(
                            SliderCircle,
                            TweenInfo.new(0.2, Enum.EasingStyle.Quad, Enum.EasingDirection.Out),
                            { Size = UDim2.new(0, 20, 0, 20) }
                        ):Play()
                        local SizeScale = math.clamp(
                            (Input.Position.X - SliderFrame.AbsolutePosition.X) / SliderFrame.AbsoluteSize.X,
                            0, 1
                        )
                        SliderFunc:Set(SliderConfig.Min + ((SliderConfig.Max - SliderConfig.Min) * SizeScale))
                    end
                end)

                SliderHitbox.InputEnded:Connect(function(Input)
                    if Input.UserInputType == Enum.UserInputType.MouseButton1 or Input.UserInputType == Enum.UserInputType.Touch then
                        Dragging = false
                        SliderConfig.Callback(SliderFunc.Value)
                        TweenService:Create(
                            SliderCircle,
                            TweenInfo.new(0.2, Enum.EasingStyle.Quad, Enum.EasingDirection.Out),
                            { Size = UDim2.new(0, 14, 0, 14) }
                        ):Play()
                    end
                end)

                UserInputService.InputChanged:Connect(function(Input)
                    if Dragging and (Input.UserInputType == Enum.UserInputType.MouseMovement or Input.UserInputType == Enum.UserInputType.Touch) then
                        local SizeScale = math.clamp(
                            (Input.Position.X - SliderFrame.AbsolutePosition.X) / SliderFrame.AbsoluteSize.X,
                            0,
                            1
                        )
                        SliderFunc:Set(SliderConfig.Min + ((SliderConfig.Max - SliderConfig.Min) * SizeScale))
                    end
                end)

                TextBox.FocusLost:Connect(function(EnterPressed)
                    local Valid = TextBox.Text:gsub("[^%d%.]", "")
                    local ValidNumber = tonumber(Valid)
                    
                    if ValidNumber then
                        SliderFunc:Set(ValidNumber)
                    else
                        TextBox.Text = tostring(SliderFunc.Value) -- Balikin ke angka sebelumnya kalau input ngawur
                    end
                end)
                SliderFunc:Set(SliderConfig.Default)
                CountItem = CountItem + 1
                SliderFunc.Type = "Slider"
                Elements[configKey] = SliderFunc
                return SliderFunc
            end

            function Items:AddInput(InputConfig)
                local InputConfig = InputConfig or {}
                InputConfig.Title = InputConfig.Title or "Title"
                InputConfig.Placeholder = InputConfig.Placeholder or nil
                InputConfig.Content = InputConfig.Content or ""
                InputConfig.Callback = InputConfig.Callback or function() end
                InputConfig.Default = InputConfig.Default or ""

                local configKey = "Input_" .. InputConfig.Title
                if ConfigData[configKey] ~= nil then
                    InputConfig.Default = ConfigData[configKey]
                end

                local InputFunc = { Value = InputConfig.Default }

                local Input = Instance.new("Frame")
                local UICorner12 = Instance.new("UICorner")
                local InputTitle = Instance.new("TextLabel")
                local InputContent = Instance.new("TextLabel") -- Tambahan Content Label
                local InputFrame = Instance.new("Frame")
                local UICorner13 = Instance.new("UICorner")
                local InputTextBox = Instance.new("TextBox")

                Input.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                Input.BackgroundTransparency = 0.935
                Input.BorderColor3 = Color3.fromRGB(0, 0, 0)
                Input.BorderSizePixel = 0
                Input.LayoutOrder = CountItem
                Input.Size = UDim2.new(1, 0, 0, 70)
                Input.Name = "Input"
                Input.Parent = SectionAdd

                UICorner12.CornerRadius = UDim.new(0, 4)
                UICorner12.Parent = Input

                -- Bagian Title
                InputTitle.Font = Enum.Font.GothamBold
                InputTitle.Text = InputConfig.Title or "TextBox"
                InputTitle.TextColor3 = Color3.fromRGB(230, 230, 230)
                InputTitle.TextSize = 13
                InputTitle.TextXAlignment = Enum.TextXAlignment.Left
                InputTitle.TextYAlignment = Enum.TextYAlignment.Top
                InputTitle.BackgroundTransparency = 1
                InputTitle.Position = UDim2.new(0, 10, 0, 8)
                InputTitle.Size = UDim2.new(1, -20, 0, 15)
                InputTitle.Name = "InputTitle"
                InputTitle.Parent = Input

                -- Bagian Content (yang tadinya hilang) + TextWrapped agar tidak kepotong
                InputContent.Font = Enum.Font.GothamBold
                InputContent.Text = InputConfig.Content
                InputContent.TextColor3 = Color3.fromRGB(255, 255, 255)
                InputContent.TextSize = 12
                InputContent.TextTransparency = 0.6
                InputContent.TextXAlignment = Enum.TextXAlignment.Left
                InputContent.TextYAlignment = Enum.TextYAlignment.Top
                InputContent.BackgroundTransparency = 1
                InputContent.Position = UDim2.new(0, 10, 0, 24)
                InputContent.Size = UDim2.new(1, -20, 0, 12)
                InputContent.TextWrapped = true -- Mencegah teks kepotong
                InputContent.Name = "InputContent"
                InputContent.Parent = Input

                -- Frame TextBox
                InputFrame.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                InputFrame.BackgroundTransparency = 0.95
                InputFrame.BorderSizePixel = 0
                InputFrame.ClipsDescendants = true
                InputFrame.Size = UDim2.new(1, -20, 0, 25)
                InputFrame.Name = "InputFrame"
                InputFrame.Parent = Input

                UICorner13.CornerRadius = UDim.new(0, 4)
                UICorner13.Parent = InputFrame

                InputTextBox.CursorPosition = -1
                InputTextBox.Font = Enum.Font.GothamBold
                InputTextBox.PlaceholderColor3 = Color3.fromRGB(120, 120, 120)
                InputTextBox.PlaceholderText = InputConfig.Placeholder or "Input Here"
                InputTextBox.Text = InputConfig.Default
                InputTextBox.TextColor3 = Color3.fromRGB(200, 200, 200)
                InputTextBox.TextSize = 12
                InputTextBox.TextXAlignment = Enum.TextXAlignment.Left
                InputTextBox.BackgroundTransparency = 1
                InputTextBox.Position = UDim2.new(0, 8, 0, 0)
                InputTextBox.Size = UDim2.new(1, -16, 1, 0)
                InputTextBox.Name = "InputTextBox"
                InputTextBox.Parent = InputFrame
                InputTextBox.ClearTextOnFocus = false

                local function UpdateHeight()
                    local contentHeight = 0
                    if InputConfig.Content and InputConfig.Content ~= "" then
                        local textWidth = InputContent.AbsoluteSize.X
                        if textWidth <= 0 then textWidth = 350 end
                        
                        local bounds = TextService:GetTextSize(
                            InputContent.Text,
                            InputContent.TextSize,
                            InputContent.Font,
                            Vector2.new(textWidth, 1000)
                        )
                        
                        contentHeight = bounds.Y
                        InputContent.Size = UDim2.new(1, -20, 0, contentHeight)
                        InputContent.Visible = true
                    else
                        InputContent.Visible = false
                    end

                    local inputY = 26 + (contentHeight > 0 and (contentHeight + 6) or 0)
                    InputFrame.Position = UDim2.new(0, 10, 0, inputY)
                    
                    Input.Size = UDim2.new(1, 0, 0, inputY + 35)
                    
                    if UpdateSizeSection then UpdateSizeSection() end
                end

                InputContent:GetPropertyChangedSignal("AbsoluteSize"):Connect(UpdateHeight)
                UpdateHeight()

                function InputFunc:Set(Value)
                    InputTextBox.Text = Value
                    InputFunc.Value = Value
                    InputConfig.Callback(Value)
                    ConfigData[configKey] = Value
                end

                InputFunc:Set(InputFunc.Value)

                InputTextBox.FocusLost:Connect(function()
                    InputFunc:Set(InputTextBox.Text)
                end)

                CountItem = CountItem + 1
                InputFunc.Type = "Input"
                Elements[configKey] = InputFunc
                return InputFunc
            end

            function Items:AddChatBox(ChatConfig)
                local ChatConfig = ChatConfig or {}
                local Title = ChatConfig.Title or "Global Chat"
                local Callback = ChatConfig.Callback or function(msg) end

                local ChatFunc = {}

                -- Main Container
                local ChatContainer = Instance.new("Frame")
                ChatContainer.Name = "ChatContainer"
                ChatContainer.Parent = SectionAdd
                ChatContainer.BackgroundColor3 = Color3.fromRGB(22, 22, 28)
                ChatContainer.BackgroundTransparency = 0.5
                ChatContainer.BorderSizePixel = 0
                ChatContainer.LayoutOrder = CountItem
                ChatContainer.Size = UDim2.new(1, 0, 0, 260) -- Tinggi chatbox
                Instance.new("UICorner", ChatContainer).CornerRadius = UDim.new(0, 6)
                local Stroke = Instance.new("UIStroke", ChatContainer)
                Stroke.Color = GuiConfig.Color
                Stroke.Transparency = 0.7
                Stroke.Thickness = 1

                -- Title bar
                local TopBar = Instance.new("Frame", ChatContainer)
                TopBar.Size = UDim2.new(1, 0, 0, 25)
                TopBar.BackgroundTransparency = 1
                
                local TitleLbl = Instance.new("TextLabel", TopBar)
                TitleLbl.Text = Title
                TitleLbl.Font = Enum.Font.GothamBold
                TitleLbl.TextSize = 12
                TitleLbl.TextColor3 = GuiConfig.Color
                TitleLbl.BackgroundTransparency = 1
                TitleLbl.Position = UDim2.new(0, 10, 0, 0)
                TitleLbl.Size = UDim2.new(1, -20, 1, 0)
                TitleLbl.TextXAlignment = Enum.TextXAlignment.Left

                local SubLbl = Instance.new("TextLabel", TopBar)
                SubLbl.Text = "Public Community"
                SubLbl.Font = Enum.Font.Gotham
                SubLbl.TextSize = 10
                SubLbl.TextColor3 = Color3.fromRGB(150, 150, 150)
                SubLbl.BackgroundTransparency = 1
                SubLbl.Position = UDim2.new(0, 10, 0, 0)
                SubLbl.Size = UDim2.new(1, -20, 1, 0)
                SubLbl.TextXAlignment = Enum.TextXAlignment.Right

                -- Chat History (Scrolling Frame)
                local ChatHistory = Instance.new("ScrollingFrame", ChatContainer)
                ChatHistory.Size = UDim2.new(1, -10, 1, -70)
                ChatHistory.Position = UDim2.new(0, 5, 0, 25)
                ChatHistory.BackgroundTransparency = 1
                ChatHistory.BorderSizePixel = 0
                ChatHistory.ScrollBarThickness = 3
                ChatHistory.ScrollBarImageColor3 = GuiConfig.Color
                
                local ChatLayout = Instance.new("UIListLayout", ChatHistory)
                ChatLayout.SortOrder = Enum.SortOrder.LayoutOrder
                ChatLayout.Padding = UDim.new(0, 5)
                
                ChatLayout:GetPropertyChangedSignal("AbsoluteContentSize"):Connect(function()
                    ChatHistory.CanvasSize = UDim2.new(0, 0, 0, ChatLayout.AbsoluteContentSize.Y + 10)
                    ChatHistory.CanvasPosition = Vector2.new(0, ChatLayout.AbsoluteContentSize.Y) -- Auto scroll kebawah
                end)

                -- Bottom Bar (Input & Button)
                local BottomBar = Instance.new("Frame", ChatContainer)
                BottomBar.Size = UDim2.new(1, -10, 0, 32)
                BottomBar.Position = UDim2.new(0, 5, 1, -38)
                BottomBar.BackgroundTransparency = 1

                local InputFrame = Instance.new("Frame", BottomBar)
                InputFrame.Size = UDim2.new(1, -65, 1, 0)
                InputFrame.BackgroundColor3 = Color3.fromRGB(30, 30, 35)
                Instance.new("UICorner", InputFrame).CornerRadius = UDim.new(0, 4)

                local TextBox = Instance.new("TextBox", InputFrame)
                TextBox.Size = UDim2.new(1, -10, 1, 0)
                TextBox.Position = UDim2.new(0, 5, 0, 0)
                TextBox.BackgroundTransparency = 1
                TextBox.Text = ""
                TextBox.PlaceholderText = "Type a message..."
                TextBox.TextColor3 = Color3.fromRGB(220, 220, 220)
                TextBox.Font = Enum.Font.Gotham
                TextBox.TextSize = 12
                TextBox.TextXAlignment = Enum.TextXAlignment.Left
                TextBox.ClearTextOnFocus = false

                local SendBtn = Instance.new("TextButton", BottomBar)
                SendBtn.Size = UDim2.new(0, 55, 1, 0)
                SendBtn.Position = UDim2.new(1, -55, 0, 0)
                SendBtn.BackgroundColor3 = GuiConfig.Color
                SendBtn.Text = "Send"
                SendBtn.Font = Enum.Font.GothamBold
                SendBtn.TextColor3 = Color3.fromRGB(20, 20, 20)
                SendBtn.TextSize = 12
                Instance.new("UICorner", SendBtn).CornerRadius = UDim.new(0, 4)

                -- Send Function
                local function FireSend()
                    local text = TextBox.Text
                    if text ~= "" and text:match("%S") then
                        Callback(text)
                        TextBox.Text = ""
                    end
                end

                SendBtn.MouseButton1Click:Connect(FireSend)
                TextBox.FocusLost:Connect(function(enterPressed)
                    if enterPressed then FireSend() end
                end)

                -- API to add messages dynamically
                function ChatFunc:AddMessage(username, message, timeStr)
                    local MsgFrame = Instance.new("Frame", ChatHistory)
                    MsgFrame.AutomaticSize = Enum.AutomaticSize.XY
                    MsgFrame.BackgroundColor3 = Color3.fromRGB(35, 38, 35)
                    MsgFrame.BackgroundTransparency = 0.6
                    Instance.new("UICorner", MsgFrame).CornerRadius = UDim.new(0, 6)
                    Instance.new("UIPadding", MsgFrame).PaddingTop = UDim.new(0, 4)
                    Instance.new("UIPadding", MsgFrame).PaddingBottom = UDim.new(0, 4)
                    Instance.new("UIPadding", MsgFrame).PaddingLeft = UDim.new(0, 6)
                    Instance.new("UIPadding", MsgFrame).PaddingRight = UDim.new(0, 6)
                    
                    local MsgLayout = Instance.new("UIListLayout", MsgFrame)
                    MsgLayout.SortOrder = Enum.SortOrder.LayoutOrder

                    local SenderLbl = Instance.new("TextLabel", MsgFrame)
                    SenderLbl.Text = username
                    SenderLbl.Font = Enum.Font.GothamBold
                    SenderLbl.TextColor3 = Color3.fromRGB(200, 200, 200)
                    SenderLbl.TextSize = 11
                    SenderLbl.BackgroundTransparency = 1
                    SenderLbl.AutomaticSize = Enum.AutomaticSize.XY
                    SenderLbl.TextXAlignment = Enum.TextXAlignment.Left
                    SenderLbl.LayoutOrder = 1

                    local ContentLbl = Instance.new("TextLabel", MsgFrame)
                    ContentLbl.Text = message
                    ContentLbl.Font = Enum.Font.Gotham
                    ContentLbl.TextColor3 = Color3.fromRGB(255, 255, 255)
                    ContentLbl.TextSize = 12
                    ContentLbl.BackgroundTransparency = 1
                    ContentLbl.AutomaticSize = Enum.AutomaticSize.XY
                    ContentLbl.TextXAlignment = Enum.TextXAlignment.Left
                    ContentLbl.TextWrapped = true
                    ContentLbl.LayoutOrder = 2

                    local TimeLbl = Instance.new("TextLabel", MsgFrame)
                    TimeLbl.Text = timeStr or os.date("%H:%M")
                    TimeLbl.Font = Enum.Font.Gotham
                    TimeLbl.TextColor3 = Color3.fromRGB(120, 120, 120)
                    TimeLbl.TextSize = 9
                    TimeLbl.BackgroundTransparency = 1
                    TimeLbl.AutomaticSize = Enum.AutomaticSize.XY
                    TimeLbl.TextXAlignment = Enum.TextXAlignment.Left
                    TimeLbl.LayoutOrder = 3
                end

                CountItem = CountItem + 1
                return ChatFunc
            end
            
            function Items:AddDropdown(DropdownConfig)
                local DropdownConfig = DropdownConfig or {}
                DropdownConfig.Title = DropdownConfig.Title or "Title"
                DropdownConfig.Content = DropdownConfig.Content or ""
                DropdownConfig.Multi = DropdownConfig.Multi or false
                DropdownConfig.Options = DropdownConfig.Options or {}
                DropdownConfig.Default = DropdownConfig.Default or (DropdownConfig.Multi and {} or nil)
                DropdownConfig.Callback = DropdownConfig.Callback or function() end

                local configKey = "Dropdown_" .. DropdownConfig.Title
                if ConfigData[configKey] ~= nil then
                    DropdownConfig.Default = ConfigData[configKey]
                end

                local DropdownFunc = { Value = DropdownConfig.Default, Options = DropdownConfig.Options }

                local Dropdown = Instance.new("Frame")
                local DropdownButton = Instance.new("TextButton")
                local UICorner10 = Instance.new("UICorner")
                local DropdownTitle = Instance.new("TextLabel")
                local DropdownContent = Instance.new("TextLabel")
                local SelectOptionsFrame = Instance.new("Frame")
                local UICorner11 = Instance.new("UICorner")
                local OptionSelecting = Instance.new("TextLabel")
                local OptionImg = Instance.new("ImageLabel")

                Dropdown.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                Dropdown.BackgroundTransparency = 0.935
                Dropdown.BorderSizePixel = 0
                Dropdown.LayoutOrder = CountItem
                Dropdown.Size = UDim2.new(1, 0, 0, 46)
                Dropdown.Name = "Dropdown"
                Dropdown.Parent = SectionAdd

                DropdownButton.Text = ""
                DropdownButton.BackgroundTransparency = 1
                DropdownButton.Size = UDim2.new(1, 0, 1, 0)
                DropdownButton.Name = "ToggleButton"
                DropdownButton.Parent = Dropdown

                UICorner10.CornerRadius = UDim.new(0, 4)
                UICorner10.Parent = Dropdown

                DropdownTitle.Font = Enum.Font.GothamBold
                DropdownTitle.Text = DropdownConfig.Title
                DropdownTitle.TextColor3 = Color3.fromRGB(230, 230, 230)
                DropdownTitle.TextSize = 13
                DropdownTitle.TextXAlignment = Enum.TextXAlignment.Left
                DropdownTitle.BackgroundTransparency = 1
                DropdownTitle.Position = UDim2.new(0, 10, 0, 10)
                DropdownTitle.Size = UDim2.new(1, -180, 0, 13)
                DropdownTitle.Name = "DropdownTitle"
                DropdownTitle.Parent = Dropdown

                DropdownContent.Font = Enum.Font.GothamBold
                DropdownContent.Text = DropdownConfig.Content
                DropdownContent.TextColor3 = Color3.fromRGB(255, 255, 255)
                DropdownContent.TextSize = 12
                DropdownContent.TextTransparency = 0.6
                DropdownContent.TextWrapped = true
                DropdownContent.TextXAlignment = Enum.TextXAlignment.Left
                DropdownContent.BackgroundTransparency = 1
                DropdownContent.Position = UDim2.new(0, 10, 0, 25)
                DropdownContent.Size = UDim2.new(1, -180, 0, 12)
                DropdownContent.Name = "DropdownContent"
                DropdownContent.Parent = Dropdown

                local function UpdateHeight()
                    local contentHeight = 0
                    if DropdownConfig.Content and DropdownConfig.Content ~= "" then
                        local textWidth = DropdownContent.AbsoluteSize.X
                        if textWidth <= 0 then textWidth = 250 end
                        
                        local bounds = TextService:GetTextSize(
                            DropdownContent.Text,
                            DropdownContent.TextSize,
                            DropdownContent.Font,
                            Vector2.new(textWidth, 1000)
                        )
                        
                        contentHeight = bounds.Y
                        DropdownContent.Size = UDim2.new(1, -180, 0, contentHeight)
                        DropdownContent.Visible = true
                    else
                        DropdownContent.Visible = false
                    end

                    local targetHeight = contentHeight > 0 and (contentHeight + 34) or 38
                    Dropdown.Size = UDim2.new(1, 0, 0, targetHeight)
                    
                    if UpdateSizeSection then UpdateSizeSection() end
                end

                DropdownContent:GetPropertyChangedSignal("AbsoluteSize"):Connect(UpdateHeight)
                UpdateHeight()

                SelectOptionsFrame.AnchorPoint = Vector2.new(1, 0.5)
                SelectOptionsFrame.BackgroundTransparency = 0.95
                SelectOptionsFrame.Position = UDim2.new(1, -7, 0.5, 0)
                SelectOptionsFrame.Size = UDim2.new(0, 148, 0, 30)
                SelectOptionsFrame.Name = "SelectOptionsFrame"
                SelectOptionsFrame.LayoutOrder = CountDropdown
                SelectOptionsFrame.Parent = Dropdown

                UICorner11.CornerRadius = UDim.new(0, 4)
                UICorner11.Parent = SelectOptionsFrame

                local optionsBuilt = false
                DropdownButton.Activated:Connect(function()
                    if not optionsBuilt then
                        optionsBuilt = true
                        DropdownFunc:SetValues(DropdownFunc.Options, DropdownFunc.Value)
                    end

                    if not MoreBlur.Visible then
                        MoreBlur.Visible = true
                        DropPageLayout:JumpToIndex(SelectOptionsFrame.LayoutOrder)
                        TweenService:Create(MoreBlur, TweenInfo.new(0.3), { BackgroundTransparency = 1 }):Play()
                        TweenService:Create(DropdownSelect, TweenInfo.new(0.3), { Position = UDim2.new(1, -11, 0.5, 0) }):Play()
                    end
                end)

                OptionSelecting.Font = Enum.Font.GothamBold
                OptionSelecting.Text = DropdownConfig.Multi and "Select Options" or "Select Option"
                OptionSelecting.TextColor3 = Color3.fromRGB(255, 255, 255)
                OptionSelecting.TextSize = 12
                OptionSelecting.TextTransparency = 0.6
                OptionSelecting.TextXAlignment = Enum.TextXAlignment.Left
                OptionSelecting.AnchorPoint = Vector2.new(0, 0.5)
                OptionSelecting.BackgroundTransparency = 1
                OptionSelecting.Position = UDim2.new(0, 5, 0.5, 0)
                OptionSelecting.Size = UDim2.new(1, -30, 1, -8)
                OptionSelecting.Name = "OptionSelecting"
                OptionSelecting.Parent = SelectOptionsFrame

                do
                    local def = DropdownFunc.Value
                    if DropdownConfig.Multi then
                        if type(def) == "table" and #def > 0 then
                            local texts = {}
                            for _, v in ipairs(def) do
                                table.insert(texts, tostring(v))
                            end
                            OptionSelecting.Text = table.concat(texts, ", ")
                        else
                            OptionSelecting.Text = "Select Options"
                        end
                    else
                        if def ~= nil and def ~= "" and def ~= "None" then
                            OptionSelecting.Text = tostring(def)
                        else
                            OptionSelecting.Text = "Select Option"
                        end
                    end
                end

                OptionImg.Image = "rbxassetid://16851841101"
                OptionImg.ImageColor3 = Color3.fromRGB(230, 230, 230)
                OptionImg.AnchorPoint = Vector2.new(1, 0.5)
                OptionImg.BackgroundTransparency = 1
                OptionImg.Position = UDim2.new(1, 0, 0.5, 0)
                OptionImg.Size = UDim2.new(0, 25, 0, 25)
                OptionImg.Name = "OptionImg"
                OptionImg.Parent = SelectOptionsFrame

                local DropdownContainer = Instance.new("Frame")
                DropdownContainer.Size = UDim2.new(1, 0, 1, 0)
                DropdownContainer.BackgroundTransparency = 1
                DropdownContainer.Parent = DropdownFolder

                local SearchBox = Instance.new("TextBox")
                SearchBox.PlaceholderText = "Search"
                SearchBox.Font = Enum.Font.Gotham
                SearchBox.Text = ""
                SearchBox.TextSize = 12
                SearchBox.TextColor3 = Color3.fromRGB(255, 255, 255)
                SearchBox.BackgroundColor3 = Color3.fromRGB(30, 30, 30)
                SearchBox.BackgroundTransparency = 0.9
                SearchBox.BorderSizePixel = 0
                SearchBox.Size = UDim2.new(1, 0, 0, 25)
                SearchBox.Position = UDim2.new(0, 0, 0, 0)
                SearchBox.ClearTextOnFocus = false
                SearchBox.Name = "SearchBox"
                SearchBox.Parent = DropdownContainer

                local ScrollSelect = Instance.new("ScrollingFrame")
                ScrollSelect.Size = UDim2.new(1, 0, 1, -30)
                ScrollSelect.Position = UDim2.new(0, 0, 0, 30)
                ScrollSelect.ScrollBarImageTransparency = 1
                ScrollSelect.BorderSizePixel = 0
                ScrollSelect.BackgroundTransparency = 1
                ScrollSelect.ScrollBarThickness = 0
                ScrollSelect.CanvasSize = UDim2.new(0, 0, 0, 0)
                ScrollSelect.Name = "ScrollSelect"
                ScrollSelect.Parent = DropdownContainer

                local UIListLayout4 = Instance.new("UIListLayout")
                UIListLayout4.Padding = UDim.new(0, 3)
                UIListLayout4.SortOrder = Enum.SortOrder.LayoutOrder
                UIListLayout4.Parent = ScrollSelect

                UIListLayout4:GetPropertyChangedSignal("AbsoluteContentSize"):Connect(function()
                    ScrollSelect.CanvasSize = UDim2.new(0, 0, 0, UIListLayout4.AbsoluteContentSize.Y)
                end)

                SearchBox:GetPropertyChangedSignal("Text"):Connect(function()
                    local query = string.lower(SearchBox.Text)
                    for _, option in pairs(ScrollSelect:GetChildren()) do
                        if option.Name == "Option" and option:FindFirstChild("OptionText") then
                            local text = string.lower(option.OptionText.Text)
                            option.Visible = query == "" or string.find(text, query, 1, true)
                        end
                    end
                    ScrollSelect.CanvasSize = UDim2.new(0, 0, 0, UIListLayout4.AbsoluteContentSize.Y)
                end)

                local DropCount = 0

                function DropdownFunc:Clear()
                    for _, DropFrame in ScrollSelect:GetChildren() do
                        if DropFrame.Name == "Option" then
                            DropFrame:Destroy()
                        end
                    end
                    DropdownFunc.Value = DropdownConfig.Multi and {} or nil
                    DropdownFunc.Options = {}
                    OptionSelecting.Text = DropdownConfig.Multi and "Select Options" or "Select Option"
                    DropCount = 0
                end

                function DropdownFunc:AddOption(option)
                    local label, value, subLabel, subColor
                    if typeof(option) == "table" and option.Label and option.Value ~= nil then
                        label    = tostring(option.Label)
                        value    = option.Value
                        subLabel = option.SubLabel
                        subColor = option.SubColor
                    else
                        label = tostring(option)
                        value = option
                    end

                    local Option = Instance.new("Frame")
                    local OptionButton = Instance.new("TextButton")
                    local OptionText = Instance.new("TextLabel")
                    local ChooseFrame = Instance.new("Frame")
                    local UIStroke15 = Instance.new("UIStroke")
                    local UICorner38 = Instance.new("UICorner")
                    local UICorner37 = Instance.new("UICorner")

                    local optionHeight = subLabel and 42 or 30

                    Option.BackgroundTransparency = 1
                    Option.Size = UDim2.new(1, 0, 0, optionHeight)
                    Option.Name = "Option"
                    Option.Parent = ScrollSelect

                    UICorner37.CornerRadius = UDim.new(0, 3)
                    UICorner37.Parent = Option

                    OptionButton.BackgroundTransparency = 1
                    OptionButton.Size = UDim2.new(1, 0, 1, 0)
                    OptionButton.Text = ""
                    OptionButton.Name = "OptionButton"
                    OptionButton.Parent = Option

                    OptionText.Font = Enum.Font.GothamBold
                    OptionText.Text = label
                    OptionText.TextSize = 13
                    OptionText.TextColor3 = Color3.fromRGB(230, 230, 230)
                    OptionText.Position = UDim2.new(0, 8, 0, subLabel and 6 or 8)
                    OptionText.Size = UDim2.new(1, -100, 0, 13)
                    OptionText.BackgroundTransparency = 1
                    OptionText.TextXAlignment = Enum.TextXAlignment.Left
                    OptionText.Name = "OptionText"
                    OptionText.Parent = Option

                    if subLabel then
                        local SubText = Instance.new("TextLabel")
                        SubText.Font = Enum.Font.GothamBold
                        SubText.Text = tostring(subLabel)
                        SubText.TextSize = 10
                        SubText.TextColor3 = subColor or Color3.fromRGB(220, 80, 80)
                        SubText.Position = UDim2.new(0, 8, 0, 22)
                        SubText.Size = UDim2.new(1, -20, 0, 12)
                        SubText.BackgroundTransparency = 1
                        SubText.TextXAlignment = Enum.TextXAlignment.Left
                        SubText.Name = "SubText"
                        SubText.Parent = Option
                    end

                    Option:SetAttribute("RealValue", value)

                    ChooseFrame.AnchorPoint = Vector2.new(0, 0.5)
                    ChooseFrame.BackgroundColor3 = GuiConfig.Color
                    ChooseFrame.Position = UDim2.new(0, 2, 0.5, 0)
                    ChooseFrame.Size = UDim2.new(0, 0, 0, 0)
                    ChooseFrame.Name = "ChooseFrame"
                    ChooseFrame.Parent = Option

                    UIStroke15.Color = GuiConfig.Color
                    UIStroke15.Thickness = 1.6
                    UIStroke15.Transparency = 0.999
                    UIStroke15.Parent = ChooseFrame
                    UICorner38.Parent = ChooseFrame

                    OptionButton.Activated:Connect(function()
                        if DropdownConfig.Multi then
                            local isSelected = table.find(DropdownFunc.Value, value)
                            
                            if value == "None" or value == "All" then
                                DropdownFunc.Value = {value}
                            else
                                if not isSelected then
                                    table.insert(DropdownFunc.Value, value)
                                    
                                    for i = #DropdownFunc.Value, 1, -1 do
                                        if DropdownFunc.Value[i] == "None" or DropdownFunc.Value[i] == "All" then
                                            table.remove(DropdownFunc.Value, i)
                                        end
                                    end
                                else
                                    for i, v in pairs(DropdownFunc.Value) do
                                        if v == value then
                                            table.remove(DropdownFunc.Value, i)
                                            break
                                        end
                                    end
                                    
                                    if #DropdownFunc.Value == 0 then
                                        table.insert(DropdownFunc.Value, "None")
                                    end
                                end
                            end
                        else
                            DropdownFunc.Value = value
                        end
                        DropdownFunc:Set(DropdownFunc.Value)
                    end)
                end

                function DropdownFunc:Set(Value)
                    if DropdownConfig.Multi then
                        DropdownFunc.Value = type(Value) == "table" and Value or {}
                    else
                        DropdownFunc.Value = (type(Value) == "table" and Value[1]) or Value
                    end

                    ConfigData[configKey] = DropdownFunc.Value

                    local texts = {}
                    for _, Drop in ScrollSelect:GetChildren() do
                        if Drop.Name == "Option" and Drop:FindFirstChild("OptionText") then
                            local v = Drop:GetAttribute("RealValue")
                            local selected = DropdownConfig.Multi and table.find(DropdownFunc.Value, v) or DropdownFunc.Value == v

                            if selected then
                                TweenService:Create(Drop.ChooseFrame, TweenInfo.new(0.2), { Size = UDim2.new(0, 1, 0, 12) }):Play()
                                TweenService:Create(Drop.ChooseFrame.UIStroke, TweenInfo.new(0.2), { Transparency = 0 }):Play()
                                TweenService:Create(Drop, TweenInfo.new(0.2), { BackgroundTransparency = 0.935 }):Play()
                                table.insert(texts, Drop.OptionText.Text)
                            else
                                TweenService:Create(Drop.ChooseFrame, TweenInfo.new(0.1), { Size = UDim2.new(0, 0, 0, 0) }):Play()
                                TweenService:Create(Drop.ChooseFrame.UIStroke, TweenInfo.new(0.1), { Transparency = 0.999 }):Play()
                                TweenService:Create(Drop, TweenInfo.new(0.1), { BackgroundTransparency = 0.999 }):Play()
                            end
                        end
                    end

                    OptionSelecting.Text = (#texts == 0)
                        and (DropdownConfig.Multi and "Select Options" or "Select Option")
                        or table.concat(texts, ", ")

                    if DropdownConfig.Callback then
                        if DropdownConfig.Multi then
                            DropdownConfig.Callback(DropdownFunc.Value)
                        else
                            local str = (DropdownFunc.Value ~= nil) and tostring(DropdownFunc.Value) or ""
                            DropdownConfig.Callback(str)
                        end
                    end
                end

                function DropdownFunc:SetValue(val)
                    self:Set(val)
                end

                function DropdownFunc:GetValue()
                    return self.Value
                end

                function DropdownFunc:SetValues(newList, selecting)
                    newList = newList or {}
                    selecting = selecting or (DropdownConfig.Multi and {} or nil)
                    DropdownFunc:Clear()
                    for _, v in ipairs(newList) do
                        DropdownFunc:AddOption(v)
                    end
                    DropdownFunc.Options = newList
                    DropdownFunc:Set(selecting)
                end

                CountItem = CountItem + 1
                CountDropdown = CountDropdown + 1
                DropdownFunc.Type = "Dropdown"
                Elements[configKey] = DropdownFunc
                return DropdownFunc
            end

            function Items:AddDivider()
                local Divider = Instance.new("Frame")
                Divider.Name = "Divider"
                Divider.Parent = SectionAdd
                Divider.AnchorPoint = Vector2.new(0.5, 0)
                Divider.Position = UDim2.new(0.5, 0, 0, 0)
                Divider.Size = UDim2.new(1, 0, 0, 2)
                Divider.BackgroundColor3 = Color3.fromRGB(255, 255, 255)
                Divider.BackgroundTransparency = 0
                Divider.BorderSizePixel = 0
                Divider.LayoutOrder = CountItem

                local UIGradient = Instance.new("UIGradient")
                UIGradient.Color = ColorSequence.new {
                    ColorSequenceKeypoint.new(0, Color3.fromRGB(20, 20, 20)),
                    ColorSequenceKeypoint.new(0.5, GuiConfig.Color),
                    ColorSequenceKeypoint.new(1, Color3.fromRGB(20, 20, 20))
                }
                UIGradient.Parent = Divider

                local UICorner = Instance.new("UICorner")
                UICorner.CornerRadius = UDim.new(0, 2)
                UICorner.Parent = Divider

                CountItem = CountItem + 1
                return Divider
            end

            function Items:AddSubSection(title)
                title = title or "Sub Section"

                local SubSection = Instance.new("Frame")
                SubSection.Name = "SubSection"
                SubSection.Parent = SectionAdd
                SubSection.BackgroundTransparency = 1
                SubSection.Size = UDim2.new(1, 0, 0, 22)
                SubSection.LayoutOrder = CountItem

                local Label = Instance.new("TextLabel")
                Label.Parent = SubSection
                Label.AnchorPoint = Vector2.new(0, 0.5)
                Label.Position = UDim2.new(0, 10, 0.5, 0)
                Label.Size = UDim2.new(1, -20, 1, 0)
                Label.BackgroundTransparency = 1
                Label.Font = Enum.Font.GothamBold
                Label.Text = title
                Label.TextColor3 = GuiConfig.Color
                Label.TextSize = 14
                Label.TextXAlignment = Enum.TextXAlignment.Left

                CountItem = CountItem + 1
                return SubSection
            end

            CountSection = CountSection + 1
            return Items
        end

        CountTab = CountTab + 1
        local safeName = TabConfig.Name:gsub("%s+", "_")
        _G[safeName] = Sections
        return Sections
    end

    --// Tag
    local TagList = Instance.new("Frame")
    TagList.Name = "TagList"
    TagList.AnchorPoint = Vector2.new(1, 0.5)
    TagList.Position = UDim2.new(1, -68, 0.5, 0)
    TagList.BackgroundTransparency = 1
    TagList.AutomaticSize = Enum.AutomaticSize.X
    TagList.Size = UDim2.new(0, 0, 1, 0)
    TagList.Parent = Top

    local TagListLayout = Instance.new("UIListLayout")
    TagListLayout.FillDirection = Enum.FillDirection.Horizontal
    TagListLayout.VerticalAlignment = Enum.VerticalAlignment.Center
    TagListLayout.HorizontalAlignment = Enum.HorizontalAlignment.Right
    TagListLayout.Padding = UDim.new(0, 4)
    TagListLayout.SortOrder = Enum.SortOrder.LayoutOrder
    TagListLayout.Parent = TagList

    local TagCount = 0

    function Tabs:Tag(TagConfig)
        TagConfig = TagConfig or {}
        TagConfig.Title  = TagConfig.Title  or "Tag"
        TagConfig.Icon   = TagConfig.Icon   or ""
        TagConfig.Color  = TagConfig.Color  or Color3.fromRGB(255, 255, 255)
        TagConfig.Radius = math.clamp(TagConfig.Radius or 13, 0, 13)

        local TagFrame = Instance.new("Frame")
        TagFrame.Name = "TagFrame"
        TagFrame.BackgroundColor3 = TagConfig.Color
        TagFrame.BackgroundTransparency = 0.15
        TagFrame.BorderSizePixel = 0
        TagFrame.AutomaticSize = Enum.AutomaticSize.X
        TagFrame.Size = UDim2.new(0, 0, 0, 22)
        TagFrame.LayoutOrder = TagCount
        TagFrame.ZIndex = 5
        TagFrame.Parent = TagList

        local TagCorner = Instance.new("UICorner")
        TagCorner.CornerRadius = UDim.new(0, TagConfig.Radius)
        TagCorner.Parent = TagFrame

        local TagStroke = Instance.new("UIStroke")
        TagStroke.Color = TagConfig.Color
        TagStroke.Thickness = 1.5
        TagStroke.Transparency = 0.5
        TagStroke.ApplyStrokeMode = Enum.ApplyStrokeMode.Border
        TagStroke.Parent = TagFrame

        local TagPadding = Instance.new("UIPadding")
        TagPadding.PaddingLeft  = UDim.new(0, 8)
        TagPadding.PaddingRight = UDim.new(0, 8)
        TagPadding.Parent = TagFrame

        local TagInnerLayout = Instance.new("UIListLayout")
        TagInnerLayout.FillDirection = Enum.FillDirection.Horizontal
        TagInnerLayout.VerticalAlignment = Enum.VerticalAlignment.Center
        TagInnerLayout.HorizontalAlignment = Enum.HorizontalAlignment.Center
        TagInnerLayout.Padding = UDim.new(0, 5)
        TagInnerLayout.SortOrder = Enum.SortOrder.LayoutOrder
        TagInnerLayout.Parent = TagFrame

        if TagConfig.Icon ~= "" then
            local iconImg = Instance.new("ImageLabel")
            iconImg.Name = "TagIcon"
            iconImg.BackgroundTransparency = 1
            iconImg.Size = UDim2.new(0, 14, 0, 14)
            iconImg.ZIndex = 6
            iconImg.LayoutOrder = 0
            if Icons[TagConfig.Icon] then
                iconImg.Image = Icons[TagConfig.Icon]
            else
                iconImg.Image = "rbxassetid://" .. tostring(TagConfig.Icon)
            end
            iconImg.ImageColor3 = Color3.fromRGB(255, 255, 255)
            iconImg.ScaleType = Enum.ScaleType.Fit
            iconImg.Parent = TagFrame
        end

        local TagLabel = Instance.new("TextLabel")
        TagLabel.Name = "TagLabel"
        TagLabel.BackgroundTransparency = 1
        TagLabel.Font = Enum.Font.GothamBold
        TagLabel.Text = TagConfig.Title
        TagLabel.TextColor3 = Color3.fromRGB(255, 255, 255)
        TagLabel.TextSize = 12
        TagLabel.AutomaticSize = Enum.AutomaticSize.X
        TagLabel.Size = UDim2.new(0, 0, 1, 0)
        TagLabel.ZIndex = 6
        TagLabel.LayoutOrder = 1
        TagLabel.Parent = TagFrame

        TagCount = TagCount + 1

        local TagFunc = {}
        function TagFunc:Set(newTitle, newColor)
            if newTitle then TagLabel.Text = newTitle end
            if newColor then
                TagFrame.BackgroundColor3 = newColor
                TagStroke.Color = newColor
            end
        end
        function TagFunc:Destroy()
            TagFrame:Destroy()
        end

        return TagFunc
    end

    function Tabs:SetToggleKey(key)
        if typeof(key) == "EnumItem" and key.EnumType == Enum.KeyCode then
            ToggleKey = key
        elseif type(key) == "string" then
            ToggleKey = Enum.KeyCode[key] or Enum.KeyCode.F3
        end
    end

    function Tabs:ShowKeybindList(visible)
        KeybindListVisible = visible == true
        if RefreshKeybindList then
            RefreshKeybindList()
        end
    end

    function Tabs:RefreshKeybindList()
        if RefreshKeybindList then
            RefreshKeybindList()
        end
    end

    function Tabs:SetKeybindListPosition(scaleX, scaleY, offsetX, offsetY)
        if KeybindListFrame then
            local sx = scaleX or 0
            local sy = scaleY or 0
            KeybindListFrame.AnchorPoint = Vector2.new(sx, sy)
            KeybindListFrame.Position = UDim2.new(sx, offsetX or 14, sy, offsetY or 70)

            if sx >= 0.5 then
                KeybindListLayout.HorizontalAlignment = Enum.HorizontalAlignment.Right
            else
                KeybindListLayout.HorizontalAlignment = Enum.HorizontalAlignment.Left
            end
            if sy >= 0.5 then
                KeybindListLayout.VerticalAlignment = Enum.VerticalAlignment.Bottom
            else
                KeybindListLayout.VerticalAlignment = Enum.VerticalAlignment.Top
            end
        end
    end

    return Tabs
end

return Menghub
